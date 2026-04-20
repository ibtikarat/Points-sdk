<?php

declare(strict_types=1);

namespace PointsApp\Points\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use PointsApp\Points\Config;
use PointsApp\Points\Exceptions\AuthenticationException;
use PointsApp\Points\Exceptions\ForbiddenException;
use PointsApp\Points\Exceptions\NetworkException;
use PointsApp\Points\Exceptions\NotFoundException;
use PointsApp\Points\Exceptions\PointsException;
use PointsApp\Points\Exceptions\RateLimitException;
use PointsApp\Points\Exceptions\ServerException;
use PointsApp\Points\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;

/**
 * Thin Guzzle-backed HTTP client that:
 *   - injects the x-api-key header
 *   - parses the Points API envelope
 *   - maps HTTP status codes to typed exceptions
 *   - retries idempotent failures (429, 5xx, network) with exponential backoff
 */
final class HttpClient
{
    private const USER_AGENT_PREFIX = 'points-php-sdk';
    public const SDK_VERSION = '0.1.0';

    private ClientInterface $guzzle;

    public function __construct(
        private readonly Config $config,
        ?ClientInterface $guzzle = null,
    ) {
        $this->guzzle = $guzzle ?? new GuzzleClient([
            'base_uri' => $this->config->baseUrl,
            'timeout' => $this->config->timeout,
            'connect_timeout' => $this->config->connectTimeout,
            'http_errors' => false,
        ]);
    }

    /**
     * @param array<string, mixed>|null $json  JSON body payload (null for no body)
     * @param array<string, mixed>      $query query-string params
     */
    public function request(
        string $method,
        string $path,
        ?array $json = null,
        array $query = [],
    ): Response {
        $attempt = 0;
        $maxAttempts = $this->config->retries + 1;
        $lastException = null;

        while ($attempt < $maxAttempts) {
            ++$attempt;

            try {
                $options = [
                    'headers' => [
                        'x-api-key' => $this->config->privateKey,
                        'Accept' => 'application/json',
                        'User-Agent' => self::USER_AGENT_PREFIX . '/' . self::SDK_VERSION . ' php/' . PHP_VERSION,
                    ],
                ];

                if ($json !== null) {
                    $options['json'] = $json;
                }
                if ($query !== []) {
                    $options['query'] = $query;
                }

                $this->config->logger->debug('points-sdk: request', [
                    'method' => $method,
                    'path' => $path,
                    'attempt' => $attempt,
                ]);

                $psrResponse = $this->guzzle->request(
                    $method,
                    $this->buildUri($path),
                    $options,
                );

                $response = $this->parseResponse($psrResponse);

                if ($this->shouldRetryStatus($response->statusCode) && $attempt < $maxAttempts) {
                    $this->sleepBackoff($attempt, $response);
                    continue;
                }

                if (!$response->isSuccess()) {
                    throw $this->mapErrorResponse($response);
                }

                return $response;
            } catch (ConnectException $e) {
                $lastException = new NetworkException(
                    'Connection failed: ' . $e->getMessage(),
                    null,
                    [],
                    null,
                    $e,
                );
                if ($attempt < $maxAttempts) {
                    $this->sleepBackoff($attempt, null);
                    continue;
                }
                throw $lastException;
            } catch (PointsException $e) {
                throw $e;
            } catch (RequestException $e) {
                $response = $e->getResponse();
                if ($response !== null) {
                    $parsed = $this->parseResponse($response);
                    if ($this->shouldRetryStatus($parsed->statusCode) && $attempt < $maxAttempts) {
                        $this->sleepBackoff($attempt, $parsed);
                        continue;
                    }
                    throw $this->mapErrorResponse($parsed);
                }
                $lastException = new NetworkException(
                    'Request failed: ' . $e->getMessage(),
                    null,
                    [],
                    null,
                    $e,
                );
                if ($attempt < $maxAttempts) {
                    $this->sleepBackoff($attempt, null);
                    continue;
                }
                throw $lastException;
            } catch (GuzzleException $e) {
                throw new NetworkException(
                    'HTTP client error: ' . $e->getMessage(),
                    null,
                    [],
                    null,
                    $e,
                );
            }
        }

        throw $lastException ?? new NetworkException('Exhausted retries with no response.');
    }

    private function buildUri(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return $this->config->baseUrl . '/' . ltrim($path, '/');
    }

    private function parseResponse(ResponseInterface $psr): Response
    {
        $rawBody = (string) $psr->getBody();
        $decoded = [];

        if ($rawBody !== '') {
            $attempt = json_decode($rawBody, true);
            if (is_array($attempt)) {
                $decoded = $attempt;
            }
        }

        return new Response(
            statusCode: $psr->getStatusCode(),
            headers: $psr->getHeaders(),
            body: $decoded,
            rawBody: $rawBody,
        );
    }

    private function shouldRetryStatus(int $status): bool
    {
        return $status === 429 || $status >= 500;
    }

    private function sleepBackoff(int $attempt, ?Response $response): void
    {
        $retryAfterMs = null;
        if ($response !== null) {
            $header = $response->header('Retry-After');
            if ($header !== null && ctype_digit($header)) {
                $retryAfterMs = ((int) $header) * 1000;
            }
        }

        $delayMs = $retryAfterMs ?? (int) min(10_000, 250 * (2 ** ($attempt - 1)));
        $jitter = random_int(0, 100);
        usleep(($delayMs + $jitter) * 1000);
    }

    private function mapErrorResponse(Response $response): PointsException
    {
        $message = $response->message();
        if ($message === '') {
            $message = 'HTTP ' . $response->statusCode;
        }
        $payload = $response->body;
        $requestId = $response->header('X-Request-Id') ?? $response->header('X-Request-ID');
        $status = $response->statusCode;

        return match (true) {
            $status === 400 => new AuthenticationException(
                $this->looksLikeAuthError($message) ? $message : $message,
                $status,
                $payload,
                $requestId,
            ),
            $status === 401 => new AuthenticationException($message, $status, $payload, $requestId),
            $status === 403 => new ForbiddenException($message, $status, $payload, $requestId),
            $status === 404 => new NotFoundException($message, $status, $payload, $requestId),
            $status === 422 => new ValidationException(
                $message,
                $this->extractValidationErrors($payload),
                $payload,
                $status,
                $requestId,
            ),
            $status === 429 => new RateLimitException(
                $message,
                $this->parseRetryAfter($response),
                $payload,
                $status,
                $requestId,
            ),
            $status >= 500 => new ServerException($message, $status, $payload, $requestId),
            default => new PointsException($message, $status, $payload, $requestId),
        };
    }

    private function looksLikeAuthError(string $message): bool
    {
        $needle = strtolower($message);

        return str_contains($needle, 'api key');
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, array<int, string>>
     */
    private function extractValidationErrors(array $payload): array
    {
        $errors = $payload['errors'] ?? [];
        if (!is_array($errors)) {
            return [];
        }

        $normalized = [];
        foreach ($errors as $field => $messages) {
            if (!is_string($field)) {
                continue;
            }
            if (is_string($messages)) {
                $normalized[$field] = [$messages];
                continue;
            }
            if (is_array($messages)) {
                $normalized[$field] = array_values(array_map('strval', $messages));
            }
        }

        return $normalized;
    }

    private function parseRetryAfter(Response $response): ?int
    {
        $header = $response->header('Retry-After');
        if ($header === null) {
            return null;
        }

        return ctype_digit($header) ? (int) $header : null;
    }
}
