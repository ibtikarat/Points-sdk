<?php

declare(strict_types=1);

namespace PointsApp\Points\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;
use PointsApp\Points\Client;
use PointsApp\Points\Config;
use Psr\Http\Message\RequestInterface;

abstract class TestCase extends BaseTestCase
{
    /** @var array<int, array{request: RequestInterface, options: array<string, mixed>}> */
    protected array $history = [];

    /**
     * @param array<int, Response|\Throwable> $responses
     */
    protected function makeClient(array $responses, array $configOverrides = []): Client
    {
        $this->history = [];
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->history));

        $guzzle = new GuzzleClient([
            'handler' => $stack,
            'http_errors' => false,
        ]);

        return new Client(
            new Config(array_merge([
                'private_key' => 'test_private_key',
                'public_key' => 'test_public_key',
                'base_url' => 'https://api.test.local',
                'timeout' => 5,
                'retries' => 0,
            ], $configOverrides)),
            $guzzle,
        );
    }

    protected function fixture(string $name): string
    {
        $path = __DIR__ . '/Fixtures/' . $name;
        if (!file_exists($path)) {
            throw new \RuntimeException("Missing fixture: {$name}");
        }

        return (string) file_get_contents($path);
    }

    protected function jsonResponse(int $status, string $fixtureName): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], $this->fixture($fixtureName));
    }

    protected function inlineJsonResponse(int $status, string $body): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], $body);
    }

    protected function lastRequest(): RequestInterface
    {
        $entry = end($this->history);
        if ($entry === false) {
            throw new \RuntimeException('No request was made.');
        }

        return $entry['request'];
    }

    protected function firstRequest(): RequestInterface
    {
        $entry = reset($this->history);
        if ($entry === false) {
            throw new \RuntimeException('No request was made.');
        }

        return $entry['request'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function lastRequestBody(): array
    {
        $body = (string) $this->lastRequest()->getBody();
        if ($body === '') {
            return [];
        }
        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : [];
    }
}
