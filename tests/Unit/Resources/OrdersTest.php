<?php

declare(strict_types=1);

namespace Papp\Points\Tests\Unit\Resources;

use InvalidArgumentException;
use Papp\Points\DTOs\Order;
use Papp\Points\DTOs\PendingEarningOrder;
use Papp\Points\Enums\PaymentMethod;
use Papp\Points\Enums\ShippingStatus;
use Papp\Points\Tests\TestCase;

final class OrdersTest extends TestCase
{
    public function test_get_returns_order_dto(): void
    {
        $client = $this->makeClient([$this->jsonResponse(200, 'order_show.json')]);

        $order = $client->orders()->get('550e8400-e29b-41d4-a716-446655440000');

        self::assertInstanceOf(Order::class, $order);
        self::assertSame('SHOP-9001', $order->orderNumber);
        self::assertSame('authorized', $order->orderStatus->value);
    }

    public function test_create_checkout_uses_public_key_in_path_and_normalizes_phone(): void
    {
        $client = $this->makeClient([$this->jsonResponse(200, 'checkout.json')]);

        $checkoutUrl = $client->orders()->createCheckout([
            'phone_number' => '+966 55 123 4567',
            'total_price' => 100,
            'order_number' => 'SHOP-1',
        ]);

        self::assertSame('https://papp.sa/checkout/abc123', $checkoutUrl);
        self::assertStringEndsWith('/api/v1/orders/checkout/test_public_key', (string) $this->lastRequest()->getUri());
        self::assertSame('551234567', $this->lastRequestBody()['phone_number']);
    }

    public function test_create_checkout_requires_public_key(): void
    {
        $client = $this->makeClient(
            [$this->jsonResponse(200, 'checkout.json')],
            ['public_key' => ''],
        );

        $this->expectException(InvalidArgumentException::class);
        $client->orders()->createCheckout([
            'total_price' => 100,
            'order_number' => 'SHOP-1',
        ]);
    }

    public function test_create_earning_returns_pending_order_dto_when_activation_is_deferred(): void
    {
        $client = $this->makeClient([$this->jsonResponse(200, 'order_pending_earning.json')]);

        $order = $client->orders()->createEarning([
            'phone_number' => '0551234567',
            'total_price' => 200,
            'order_number' => 'SHOP-9002',
            'products' => [
                ['product_name' => 'Item', 'product_price' => 200, 'quantity' => 1],
            ],
        ]);

        self::assertInstanceOf(PendingEarningOrder::class, $order);
        self::assertSame('SHOP-9002', $order->merchantOrderNumber);
    }

    public function test_complete_serializes_payment_method_enum(): void
    {
        $client = $this->makeClient([$this->jsonResponse(200, 'order_authorize.json')]);

        $client->orders()->complete('550e8400-e29b-41d4-a716-446655440000', PaymentMethod::Mada);

        self::assertSame('2', $this->lastRequestBody()['payment_method']);
    }

    public function test_update_shipping_status_serializes_enum_value(): void
    {
        $client = $this->makeClient([$this->jsonResponse(200, 'order_authorize.json')]);

        $client->orders()->updateShippingStatus('550e8400-e29b-41d4-a716-446655440000', ShippingStatus::Delivered);

        self::assertSame('delivered', $this->lastRequestBody()['status']);
    }
}
