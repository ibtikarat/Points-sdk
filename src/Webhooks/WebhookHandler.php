<?php

declare(strict_types=1);

namespace Papp\Points\Webhooks;

use DateTimeImmutable;
use Papp\Points\Enums\WebhookEvent;
use Papp\Points\Exceptions\InvalidSignatureException;
use Papp\Points\Exceptions\PointsException;
use Papp\Points\Webhooks\Events\MerchantEvent;
use Papp\Points\Webhooks\Events\OrderEvent;
use Papp\Points\Webhooks\Events\WebhookPayload;

/**
 * Verifies and parses incoming webhook requests from the Points platform.
 *
 * The backend uses a shared-secret scheme (not HMAC): the webhook registration
 * response returns a 60-character secret, and the backend sends that exact
 * value in the `X-Webhook-Secret` request header. The handler performs a
 * timing-safe comparison against the secret the merchant has on file.
 *
 * Also expects an `X-Webhook-Event` header carrying the event name.
 */
final class WebhookHandler
{
    public const HEADER_SECRET = 'X-Webhook-Secret';
    public const HEADER_EVENT = 'X-Webhook-Event';

    public function __construct(private readonly string $webhookSecret)
    {
        if ($this->webhookSecret === '') {
            throw new \InvalidArgumentException('WebhookHandler: secret must not be empty.');
        }
    }

    /**
     * Verify + decode an incoming webhook.
     *
     * @throws InvalidSignatureException when the secret header is missing or mismatched
     * @throws PointsException           when the payload is not valid JSON
     */
    public function parse(string $rawPayload, string $secretHeader, ?string $eventHeader = null): WebhookPayload
    {
        $this->verify($secretHeader);

        $decoded = json_decode($rawPayload, true);
        if (!is_array($decoded)) {
            throw new PointsException('Webhook payload is not valid JSON.');
        }

        $eventName = $eventHeader !== null && $eventHeader !== ''
            ? $eventHeader
            : (string) ($decoded['event'] ?? '');

        $event = WebhookEvent::tryFrom($eventName);
        if ($event === null) {
            throw new PointsException(
                sprintf('Unknown webhook event "%s".', $eventName),
            );
        }

        $timestamp = null;
        if (isset($decoded['timestamp']) && is_string($decoded['timestamp']) && $decoded['timestamp'] !== '') {
            try {
                $timestamp = new DateTimeImmutable($decoded['timestamp']);
            } catch (\Exception) {
                $timestamp = null;
            }
        }

        if ($event->isMerchantEvent()) {
            $merchant = [];
            if (isset($decoded['merchant']) && is_array($decoded['merchant'])) {
                /** @var array<string, mixed> $merchant */
                $merchant = $decoded['merchant'];
            }
            $data = [];
            if (isset($decoded['data']) && is_array($decoded['data'])) {
                /** @var array<string, mixed> $data */
                $data = $decoded['data'];
            }

            return new MerchantEvent($event, $merchant, $data, $timestamp, $decoded);
        }

        $order = [];
        if (isset($decoded['order']) && is_array($decoded['order'])) {
            /** @var array<string, mixed> $order */
            $order = $decoded['order'];
        }

        return new OrderEvent($event, $order, $timestamp, $decoded);
    }

    /**
     * Timing-safe comparison between the expected secret and the header.
     */
    public function verify(string $secretHeader): void
    {
        if ($secretHeader === '') {
            throw new InvalidSignatureException('Missing X-Webhook-Secret header.');
        }
        if (!hash_equals($this->webhookSecret, $secretHeader)) {
            throw new InvalidSignatureException('Webhook secret mismatch.');
        }
    }
}
