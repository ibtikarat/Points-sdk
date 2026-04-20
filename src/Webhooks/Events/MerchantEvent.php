<?php

declare(strict_types=1);

namespace Papp\Points\Webhooks\Events;

use DateTimeImmutable;
use Papp\Points\Enums\WebhookEvent;

final class MerchantEvent implements WebhookPayload
{
    /**
     * @param array<string, mixed> $merchant
     * @param array<string, mixed> $data
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly WebhookEvent $event,
        public readonly array $merchant,
        public readonly array $data,
        public readonly ?DateTimeImmutable $timestamp,
        public readonly array $raw,
    ) {
    }

    public function event(): WebhookEvent
    {
        return $this->event;
    }

    public function raw(): array
    {
        return $this->raw;
    }
}
