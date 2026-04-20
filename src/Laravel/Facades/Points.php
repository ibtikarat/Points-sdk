<?php

declare(strict_types=1);

namespace PointsApp\Points\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use PointsApp\Points\Client;
use PointsApp\Points\Resources\Orders;
use PointsApp\Points\Resources\Webhooks;

/**
 * @method static Orders   orders()
 * @method static Webhooks webhooks()
 *
 * @see Client
 */
final class Points extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
