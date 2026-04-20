<?php

declare(strict_types=1);

namespace PointsApp\Points\Enums;

/**
 * Accepted values for the POST /orders/{uuid}/status endpoint.
 */
enum ShippingStatus: string
{
    case New = 'new';
    case LicenseInProgress = 'license_in_progress';
    case ReadyShipping = 'ready_shipping';
    case DeliveryInProgress = 'delivery_is_in_progress';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}
