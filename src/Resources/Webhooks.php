<?php

declare(strict_types=1);

namespace Papp\Points\Resources;

use Papp\Points\DTOs\Paginated;
use Papp\Points\DTOs\Webhook;

/**
 * /api/v1/webhooks/* endpoints (merchant-registered webhook URLs).
 *
 * Not to be confused with `Papp\Points\Webhooks\WebhookHandler` which verifies
 * incoming webhook requests on the merchant side.
 */
final class Webhooks extends Resource
{
    /**
     * @return Paginated<Webhook>
     */
    public function list(int $perPage = 15): Paginated
    {
        $response = $this->http->request('GET', '/api/v1/webhooks', null, ['per_page' => $perPage]);
        $data = $response->data();

        $items = [];
        if (is_array($data)) {
            foreach ($data as $row) {
                if (is_array($row)) {
                    $items[] = Webhook::fromArray($row);
                }
            }
        }

        $meta = isset($response->body['meta']) && is_array($response->body['meta'])
            ? $response->body['meta']
            : [];
        $links = isset($response->body['links']) && is_array($response->body['links'])
            ? $response->body['links']
            : [];

        return new Paginated($items, $meta, $links);
    }

    /**
     * @param array{name: string, url: string} $payload
     */
    public function create(array $payload): Webhook
    {
        $response = $this->http->request('POST', '/api/v1/webhooks', $payload);

        return Webhook::fromArray($this->extractResource($response->data()));
    }

    public function get(string $uuid): Webhook
    {
        $response = $this->http->request('GET', '/api/v1/webhooks/' . rawurlencode($uuid));

        return Webhook::fromArray($this->extractResource($response->data()));
    }

    /**
     * @param array{name?: string, url?: string} $payload
     */
    public function update(string $uuid, array $payload): Webhook
    {
        $response = $this->http->request('PATCH', '/api/v1/webhooks/' . rawurlencode($uuid), $payload);

        return Webhook::fromArray($this->extractResource($response->data()));
    }

    public function delete(string $uuid): void
    {
        $this->http->request('DELETE', '/api/v1/webhooks/' . rawurlencode($uuid));
    }

    /**
     * @return array<string, mixed>
     */
    private function extractResource(mixed $data): array
    {
        if (!is_array($data)) {
            return [];
        }
        if (isset($data['uuid']) || isset($data['url'])) {
            return $data;
        }
        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        return $data;
    }
}
