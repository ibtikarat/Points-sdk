<?php

declare(strict_types=1);

namespace PointsApp\Points\Resources;

use InvalidArgumentException;
use PointsApp\Points\Config;
use PointsApp\Points\DTOs\Order;
use PointsApp\Points\DTOs\PendingEarningOrder;
use PointsApp\Points\Enums\PaymentMethod;
use PointsApp\Points\Enums\ShippingStatus;
use PointsApp\Points\Http\HttpClient;
use PointsApp\Points\Support\PhoneNormalizer;

/**
 * /api/v1/orders/* endpoints.
 */
final class Orders extends Resource
{
    public function __construct(
        HttpClient $http,
        private readonly Config $config,
    ) {
        parent::__construct($http);
    }

    /**
     * Create a checkout order (public_key protected — no x-api-key required).
     * Returns the redirect URL the customer should be sent to.
     *
     * @param array<string, mixed> $payload see the docs for the full shape
     */
    public function createCheckout(array $payload, ?string $publicKey = null): string
    {
        $key = $publicKey ?? $this->config->publicKey;
        if ($key === null || $key === '') {
            throw new InvalidArgumentException(
                'createCheckout requires a public_key: either pass it to this call or set "public_key" on the Config.',
            );
        }

        $payload = $this->normalizePhone($payload, required: false);

        $response = $this->http->request('POST', '/api/v1/orders/checkout/' . rawurlencode($key), $payload);
        $data = $response->data();

        if (is_array($data)) {
            if (isset($data['checkout_url']) && is_string($data['checkout_url'])) {
                return $data['checkout_url'];
            }
            if (isset($data['data']['checkout_url']) && is_string($data['data']['checkout_url'])) {
                return $data['data']['checkout_url'];
            }
        }

        throw new \RuntimeException('createCheckout: response did not include a checkout_url.');
    }

    /**
     * GET /api/v1/orders/{uuid}
     */
    public function get(string $uuid): Order
    {
        $response = $this->http->request('GET', '/api/v1/orders/' . rawurlencode($uuid));

        return Order::fromArray($this->extractOrderPayload($response->data()));
    }

    /**
     * POST /api/v1/orders/earning — create an earning order.
     * Returns either an Order (customer already active) or PendingEarningOrder.
     *
     * @param array<string, mixed> $payload
     */
    public function createEarning(array $payload): Order|PendingEarningOrder
    {
        $payload = $this->normalizePhone($payload, required: true);

        $response = $this->http->request('POST', '/api/v1/orders/earning', $payload);
        $data = $response->data();

        if (is_array($data) && isset($data['pending_client_activation']) && $data['pending_client_activation'] === true) {
            $pending = $data['pending_earning_order'] ?? [];
            if (is_array($pending)) {
                return PendingEarningOrder::fromArray($pending);
            }
        }

        return Order::fromArray($this->extractOrderPayload($data));
    }

    public function authorize(string $uuid): Order
    {
        return $this->action($uuid, 'authorize');
    }

    public function capture(string $uuid): Order
    {
        return $this->action($uuid, 'capture');
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function cancel(string $uuid, array $payload = []): Order
    {
        return $this->action($uuid, 'cancel', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function complete(string $uuid, array|PaymentMethod|null $payload = null): Order
    {
        if ($payload instanceof PaymentMethod) {
            $payload = ['payment_method' => $payload->value];
        } elseif (is_array($payload) && isset($payload['payment_method']) && $payload['payment_method'] instanceof PaymentMethod) {
            $payload['payment_method'] = $payload['payment_method']->value;
        }

        return $this->action($uuid, 'complete', $payload ?? []);
    }

    /**
     * @param array<string, mixed> $payload optional { amount?: float, reason?: string }
     */
    public function refund(string $uuid, array $payload = []): Order
    {
        return $this->action($uuid, 'refund', $payload);
    }

    public function updateShippingStatus(string $uuid, ShippingStatus|string $status): Order
    {
        $value = $status instanceof ShippingStatus ? $status->value : $status;

        return $this->action($uuid, 'status', ['status' => $value]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function action(string $uuid, string $action, array $payload = []): Order
    {
        $response = $this->http->request(
            'POST',
            '/api/v1/orders/' . rawurlencode($uuid) . '/' . $action,
            $payload,
        );

        return Order::fromArray($this->extractOrderPayload($response->data()));
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function normalizePhone(array $payload, bool $required): array
    {
        $phone = $payload['phone_number'] ?? null;
        if ($phone === null || $phone === '') {
            if ($required) {
                throw new InvalidArgumentException('phone_number is required.');
            }

            return $payload;
        }
        if (!is_string($phone)) {
            throw new InvalidArgumentException('phone_number must be a string.');
        }

        $payload['phone_number'] = PhoneNormalizer::normalize($phone);

        return $payload;
    }

    /**
     * The API sometimes wraps the order inside { data: {...} } (resource
     * envelope) and sometimes returns it at the top level. Normalize both.
     *
     * @return array<string, mixed>
     */
    private function extractOrderPayload(mixed $data): array
    {
        if (!is_array($data)) {
            return [];
        }
        if (isset($data['uuid']) || isset($data['order_status'])) {
            return $data;
        }
        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        return $data;
    }
}
