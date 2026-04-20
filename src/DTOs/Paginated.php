<?php

declare(strict_types=1);

namespace PointsApp\Points\DTOs;

/**
 * Generic paginated collection using Laravel-style meta/links envelopes.
 *
 * @template T
 */
final class Paginated
{
    /**
     * @param array<int, T>        $items
     * @param array<string, mixed> $meta
     * @param array<string, mixed> $links
     */
    public function __construct(
        public readonly array $items,
        public readonly array $meta,
        public readonly array $links,
    ) {
    }

    public function currentPage(): int
    {
        return (int) ($this->meta['current_page'] ?? 1);
    }

    public function lastPage(): int
    {
        return (int) ($this->meta['last_page'] ?? 1);
    }

    public function perPage(): int
    {
        return (int) ($this->meta['per_page'] ?? count($this->items));
    }

    public function total(): int
    {
        return (int) ($this->meta['total'] ?? count($this->items));
    }

    public function hasMorePages(): bool
    {
        return $this->currentPage() < $this->lastPage();
    }
}
