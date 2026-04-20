<?php

declare(strict_types=1);

namespace PointsApp\Points\DTOs;

final class OrderItem
{
    public function __construct(
        public readonly string $productName,
        public readonly float $productPrice,
        public readonly int $quantity,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productName: (string) ($data['product_name'] ?? ''),
            productPrice: (float) ($data['product_price'] ?? 0.0),
            quantity: (int) ($data['quantity'] ?? 0),
        );
    }

    /**
     * @return array{product_name: string, product_price: float, quantity: int}
     */
    public function toArray(): array
    {
        return [
            'product_name' => $this->productName,
            'product_price' => $this->productPrice,
            'quantity' => $this->quantity,
        ];
    }
}
