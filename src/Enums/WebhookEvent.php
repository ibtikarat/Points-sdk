<?php

declare(strict_types=1);

namespace PointsApp\Points\Enums;

/**
 * Event names carried in the X-Webhook-Event header and in the "event" field
 * of the payload.
 */
enum WebhookEvent: string
{
    // Order lifecycle
    case OrderApproved = 'approved';
    case OrderAuthorized = 'authorized';
    case OrderCaptured = 'captured';
    case OrderCancelled = 'cancelled';
    case OrderCompleted = 'completed';
    case OrderRefunded = 'refunded';
    case OrderShippingStatusUpdated = 'shipping_status_updated';

    // Merchant-level
    case MerchantDebtExceeded = 'debt_exceeded';
    case MerchantLowBalanceWarning = 'low_balance_warning';

    public function isOrderEvent(): bool
    {
        return match ($this) {
            self::OrderApproved,
            self::OrderAuthorized,
            self::OrderCaptured,
            self::OrderCancelled,
            self::OrderCompleted,
            self::OrderRefunded,
            self::OrderShippingStatusUpdated => true,
            default => false,
        };
    }

    public function isMerchantEvent(): bool
    {
        return !$this->isOrderEvent();
    }
}
