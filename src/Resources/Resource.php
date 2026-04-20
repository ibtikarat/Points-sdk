<?php

declare(strict_types=1);

namespace Papp\Points\Resources;

use Papp\Points\Http\HttpClient;

abstract class Resource
{
    public function __construct(protected readonly HttpClient $http)
    {
    }
}
