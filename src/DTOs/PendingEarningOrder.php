<?php

declare(strict_types=1);

namespace PointsApp\Points\DTOs;

/**
 * Returned by POST /orders/earning when the customer hasn't activated their
 * client account yet. The order exists in "pending" form until the customer
 * engages with the consumer app.
 */
final class PendingEarningOrder
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $merchantOrderNumber,
        public readonly float $totalPrice,
        public readonly float $totalPoints,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            merchantOrderNumber: isset($data['merchant_order_number'])
                ? (string) $data['merchant_order_number']
                : null,
            totalPrice: (float) ($data['total_price'] ?? 0.0),
            totalPoints: (float) ($data['total_points'] ?? 0.0),
        );
    }
}
