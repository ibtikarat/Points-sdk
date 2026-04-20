<?php

declare(strict_types=1);

namespace PointsApp\Points\DTOs;

use DateTimeImmutable;
use PointsApp\Points\Enums\OrderStatus;
use PointsApp\Points\Enums\OrderType;

final class Order
{
    /**
     * @param array<int, OrderItem> $items
     * @param array<string, mixed>  $metadata
     * @param array<string, mixed>  $raw      untouched API payload for forward compatibility
     */
    public function __construct(
        public readonly ?int $id,
        public readonly string $uuid,
        public readonly ?string $referenceNumber,
        public readonly OrderStatus $orderStatus,
        public readonly ?string $orderNumber,
        public readonly ?OrderType $type,
        public readonly float $totalPrice,
        public readonly float $totalPoints,
        public readonly array $metadata,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly array $items,
        public readonly array $raw,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<int, OrderItem> $items */
        $items = [];
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (is_array($item)) {
                    $items[] = OrderItem::fromArray($item);
                }
            }
        }

        $status = OrderStatus::tryFrom((string) ($data['order_status'] ?? ''))
            ?? OrderStatus::New;

        $type = null;
        if (isset($data['type'])) {
            $type = OrderType::tryFrom((int) $data['type']);
        }

        $metadata = [];
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            /** @var array<string, mixed> $metadata */
            $metadata = $data['metadata'];
        }

        $createdAt = null;
        if (isset($data['created_at']) && is_string($data['created_at']) && $data['created_at'] !== '') {
            try {
                $createdAt = new DateTimeImmutable($data['created_at']);
            } catch (\Exception) {
                $createdAt = null;
            }
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            uuid: (string) ($data['uuid'] ?? ''),
            referenceNumber: isset($data['reference_number']) ? (string) $data['reference_number'] : null,
            orderStatus: $status,
            orderNumber: isset($data['order_number']) ? (string) $data['order_number'] : null,
            type: $type,
            totalPrice: (float) ($data['total_price'] ?? 0.0),
            totalPoints: (float) ($data['total_points'] ?? 0.0),
            metadata: $metadata,
            createdAt: $createdAt,
            items: $items,
            raw: $data,
        );
    }
}
