<?php

declare(strict_types=1);

namespace PointsApp\Points\DTOs;

final class ShippingAddress
{
    public function __construct(
        public readonly ?string $city = null,
        public readonly ?string $line1 = null,
    ) {
    }

    /**
     * @param array<string, mixed>|null $data
     */
    public static function fromArray(?array $data): ?self
    {
        if ($data === null || $data === []) {
            return null;
        }

        $city = $data['city'] ?? null;
        $line1 = $data['line1'] ?? null;

        return new self(
            city: is_string($city) ? $city : null,
            line1: is_string($line1) ? $line1 : null,
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $out = [];
        if ($this->city !== null) {
            $out['city'] = $this->city;
        }
        if ($this->line1 !== null) {
            $out['line1'] = $this->line1;
        }

        return $out;
    }
}
