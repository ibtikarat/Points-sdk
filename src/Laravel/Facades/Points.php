<?php

declare(strict_types=1);

namespace Papp\Points\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Papp\Points\Client;
use Papp\Points\Resources\Orders;
use Papp\Points\Resources\Webhooks;

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
