<?php

declare(strict_types=1);

namespace Papp\Points\Tests\Integration;

use Papp\Points\DTOs\Order;
use Papp\Points\DTOs\Webhook;
use Papp\Points\Tests\TestCase;
use Papp\Points\Webhooks\Events\OrderEvent;
use Papp\Points\Webhooks\WebhookHandler;

final class FixtureCompatibilityTest extends TestCase
{
    public function test_order_fixture_maps_to_dto(): void
    {
        /** @var array{data: array<string, mixed>} $payload */
        $payload = json_decode($this->fixture('order_show.json'), true);

        $order = Order::fromArray($payload['data']);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $order->uuid);
        self::assertSame(1, $order->type?->value);
    }

    public function test_webhook_fixture_maps_to_dto(): void
    {
        /** @var array{data: array<string, mixed>} $payload */
        $payload = json_decode($this->fixture('webhook_show.json'), true);

        $webhook = Webhook::fromArray($payload['data']);

        self::assertSame('https://merchant.example/points/webhook', $webhook->url);
    }

    public function test_webhook_payload_fixture_parses(): void
    {
        $handler = new WebhookHandler('top-secret');

        $event = $handler->parse($this->fixture('webhook_event_order.json'), 'top-secret');

        self::assertInstanceOf(OrderEvent::class, $event);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $event->orderUuid());
    }
}
