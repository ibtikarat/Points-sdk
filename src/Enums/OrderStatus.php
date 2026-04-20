<?php

declare(strict_types=1);

namespace PointsApp\Points\Enums;

/**
 * Lifecycle status of an order as returned by the API.
 */
enum OrderStatus: string
{
    case New = 'new';
    case Approved = 'approved';
    case Authorized = 'authorized';
    case Captured = 'captured';
    case Cancelled = 'cancelled';
    case FullyRefunded = 'fully_refunded';
    case PartiallyRefunded = 'partially_refunded';
}
