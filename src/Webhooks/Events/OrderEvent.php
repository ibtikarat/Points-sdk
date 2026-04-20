<?php

declare(strict_types=1);

namespace Papp\Points\Webhooks\Events;

use DateTimeImmutable;
use Papp\Points\Enums\WebhookEvent;

final class OrderEvent implements WebhookPayload
{
    /**
     * @param array<string, mixed> $order
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly WebhookEvent $event,
        public readonly array $order,
        public readonly ?DateTimeImmutable $timestamp,
        public readonly array $raw,
    ) {
    }

    public function event(): WebhookEvent
    {
        return $this->event;
    }

    public function orderUuid(): string
    {
        return (string) ($this->order['id'] ?? '');
    }

    public function orderStatus(): string
    {
        return (string) ($this->order['order_status'] ?? '');
    }

    public function raw(): array
    {
        return $this->raw;
    }
}
