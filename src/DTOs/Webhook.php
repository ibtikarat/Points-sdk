<?php

declare(strict_types=1);

namespace PointsApp\Points\DTOs;

use DateTimeImmutable;

final class Webhook
{
    public function __construct(
        public readonly string $uuid,
        public readonly ?int $merchantId,
        public readonly string $name,
        public readonly string $url,
        public readonly ?string $secret,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            uuid: (string) ($data['uuid'] ?? ''),
            merchantId: isset($data['merchant_id']) ? (int) $data['merchant_id'] : null,
            name: (string) ($data['name'] ?? ''),
            url: (string) ($data['url'] ?? ''),
            secret: isset($data['secret']) ? (string) $data['secret'] : null,
            createdAt: self::parseDate($data['created_at'] ?? null),
            updatedAt: self::parseDate($data['updated_at'] ?? null),
        );
    }

    private static function parseDate(mixed $value): ?DateTimeImmutable
    {
        if (!is_string($value) || $value === '') {
            return null;
        }
        try {
            return new DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }
}
