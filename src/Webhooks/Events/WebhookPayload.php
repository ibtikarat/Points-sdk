<?php

declare(strict_types=1);

namespace PointsApp\Points\Webhooks\Events;

use PointsApp\Points\Enums\WebhookEvent;

interface WebhookPayload
{
    public function event(): WebhookEvent;

    /**
     * @return array<string, mixed>
     */
    public function raw(): array;
}
