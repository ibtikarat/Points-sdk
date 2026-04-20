<?php

declare(strict_types=1);

namespace Papp\Points\Tests\Unit\Webhooks;

use Papp\Points\Enums\WebhookEvent;
use Papp\Points\Exceptions\InvalidSignatureException;
use Papp\Points\Exceptions\PointsException;
use Papp\Points\Tests\TestCase;
use Papp\Points\Webhooks\Events\MerchantEvent;
use Papp\Points\Webhooks\Events\OrderEvent;
use Papp\Points\Webhooks\WebhookHandler;

final class WebhookHandlerTest extends TestCase
{
    public function test_parses_order_event(): void
    {
        $handler = new WebhookHandler('top-secret');

        $event = $handler->parse(
            $this->fixture('webhook_event_order.json'),
            'top-secret',
            WebhookEvent::OrderApproved->value,
        );

        self::assertInstanceOf(OrderEvent::class, $event);
        self::assertSame('approved', $event->event()->value);
        self::assertSame('approved', $event->orderStatus());
    }

    public function test_parses_merchant_event(): void
    {
        $handler = new WebhookHandler('top-secret');

        $event = $handler->parse(
            $this->fixture('webhook_event_merchant.json'),
            'top-secret',
            WebhookEvent::MerchantLowBalanceWarning->value,
        );

        self::assertInstanceOf(MerchantEvent::class, $event);
        self::assertSame('Demo Store', $event->merchant['name']);
    }

    public function test_rejects_invalid_secret(): void
    {
        $handler = new WebhookHandler('top-secret');

        $this->expectException(InvalidSignatureException::class);
        $handler->parse($this->fixture('webhook_event_order.json'), 'wrong-secret');
    }

    public function test_rejects_unknown_event(): void
    {
        $handler = new WebhookHandler('top-secret');

        $this->expectException(PointsException::class);
        $handler->parse($this->fixture('webhook_event_order.json'), 'top-secret', 'unknown_event');
    }
}
