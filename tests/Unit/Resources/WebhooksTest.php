<?php

declare(strict_types=1);

namespace Papp\Points\Tests\Unit\Resources;

use Papp\Points\DTOs\Paginated;
use Papp\Points\DTOs\Webhook;
use Papp\Points\Tests\TestCase;

final class WebhooksTest extends TestCase
{
    public function test_list_returns_paginated_webhooks(): void
    {
        $client = $this->makeClient([$this->jsonResponse(200, 'webhook_list.json')]);

        $webhooks = $client->webhooks()->list();

        self::assertInstanceOf(Paginated::class, $webhooks);
        self::assertCount(2, $webhooks->items);
        self::assertSame(2, $webhooks->total());
    }

    public function test_get_returns_webhook_dto(): void
    {
        $client = $this->makeClient([$this->jsonResponse(200, 'webhook_show.json')]);

        $webhook = $client->webhooks()->get('aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');

        self::assertInstanceOf(Webhook::class, $webhook);
        self::assertSame('Order events', $webhook->name);
    }

    public function test_create_posts_payload(): void
    {
        $client = $this->makeClient([$this->jsonResponse(200, 'webhook_show.json')]);

        $webhook = $client->webhooks()->create([
            'name' => 'Orders',
            'url' => 'https://merchant.example/points/webhook',
        ]);

        self::assertSame('Orders', $this->lastRequestBody()['name']);
        self::assertInstanceOf(Webhook::class, $webhook);
    }

    public function test_delete_issues_delete_request(): void
    {
        $client = $this->makeClient([
            $this->inlineJsonResponse(200, '{"status":true,"message":"","appended_data":{},"data":null}'),
        ]);

        $client->webhooks()->delete('aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');

        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }
}
