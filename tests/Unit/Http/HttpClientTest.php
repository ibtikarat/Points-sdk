<?php

declare(strict_types=1);

namespace Papp\Points\Tests\Unit\Http;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Papp\Points\Exceptions\AuthenticationException;
use Papp\Points\Exceptions\ForbiddenException;
use Papp\Points\Exceptions\NetworkException;
use Papp\Points\Exceptions\NotFoundException;
use Papp\Points\Exceptions\RateLimitException;
use Papp\Points\Exceptions\ServerException;
use Papp\Points\Exceptions\ValidationException;
use Papp\Points\Tests\TestCase;

final class HttpClientTest extends TestCase
{
    public function test_sends_api_key_header(): void
    {
        $client = $this->makeClient([
            $this->jsonResponse(200, 'order_show.json'),
        ]);

        $client->http()->request('GET', '/api/v1/orders/abc');

        self::assertSame('test_private_key', $this->lastRequest()->getHeaderLine('x-api-key'));
        self::assertStringContainsString('points-php-sdk', $this->lastRequest()->getHeaderLine('User-Agent'));
    }

    public function test_maps_400_to_auth_exception(): void
    {
        $client = $this->makeClient([
            $this->jsonResponse(400, 'error_auth.json'),
        ]);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid or inactive API key');
        $client->http()->request('GET', '/api/v1/orders/abc');
    }

    public function test_maps_403_to_forbidden(): void
    {
        $client = $this->makeClient([
            $this->inlineJsonResponse(403, '{"status":false,"message":"Merchant not published","appended_data":{}}'),
        ]);

        $this->expectException(ForbiddenException::class);
        $client->http()->request('GET', '/api/v1/orders/abc');
    }

    public function test_maps_404_to_not_found(): void
    {
        $client = $this->makeClient([
            $this->inlineJsonResponse(404, '{"status":false,"message":"Not found","appended_data":{}}'),
        ]);

        $this->expectException(NotFoundException::class);
        $client->http()->request('GET', '/api/v1/orders/abc');
    }

    public function test_maps_422_to_validation_with_errors(): void
    {
        $client = $this->makeClient([
            $this->jsonResponse(422, 'error_validation.json'),
        ]);

        try {
            $client->http()->request('POST', '/api/v1/orders/earning', ['total_price' => 10]);
            self::fail('Expected ValidationException');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('phone_number', $e->getErrors());
            self::assertSame(['The phone number field is required.'], $e->getErrors()['phone_number']);
        }
    }

    public function test_retries_on_5xx_then_succeeds(): void
    {
        $client = $this->makeClient([
            $this->inlineJsonResponse(503, '{"status":false,"message":"Service unavailable","appended_data":{}}'),
            $this->jsonResponse(200, 'order_show.json'),
        ], ['retries' => 1]);

        $response = $client->http()->request('GET', '/api/v1/orders/abc');

        self::assertSame(200, $response->statusCode);
        self::assertCount(2, $this->history);
    }

    public function test_maps_5xx_to_server_exception_after_retries(): void
    {
        $client = $this->makeClient([
            $this->inlineJsonResponse(500, '{"status":false,"message":"Internal","appended_data":{}}'),
        ], ['retries' => 0]);

        $this->expectException(ServerException::class);
        $client->http()->request('GET', '/api/v1/orders/abc');
    }

    public function test_maps_429_to_rate_limit(): void
    {
        $client = $this->makeClient([
            new Response(429, ['Retry-After' => '7', 'Content-Type' => 'application/json'], '{"status":false,"message":"Too many","appended_data":{}}'),
        ], ['retries' => 0]);

        try {
            $client->http()->request('GET', '/api/v1/orders/abc');
            self::fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            self::assertSame(7, $e->getRetryAfter());
        }
    }

    public function test_connect_exception_becomes_network_exception(): void
    {
        $client = $this->makeClient([
            new ConnectException('DNS boom', new Request('GET', '/')),
        ], ['retries' => 0]);

        $this->expectException(NetworkException::class);
        $client->http()->request('GET', '/api/v1/orders/abc');
    }
}
