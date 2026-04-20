<?php

declare(strict_types=1);

namespace PointsApp\Points\Enums;

/**
 * Order type as returned by the API (integer).
 *
 *   1 = EARNING   - customer earns points from a purchase
 *   2 = REPLACING - customer redeems points for goods
 */
enum OrderType: int
{
    case Earning = 1;
    case Replacing = 2;
}
