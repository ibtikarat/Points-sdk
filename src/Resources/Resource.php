<?php

declare(strict_types=1);

namespace PointsApp\Points\Resources;

use PointsApp\Points\Http\HttpClient;

abstract class Resource
{
    public function __construct(protected readonly HttpClient $http)
    {
    }
}
