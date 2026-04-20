<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Example Laravel Usage
|--------------------------------------------------------------------------
|
| In a Laravel app after `composer require papp/points-sdk`, add:
|
| config/services.php
| 'points' => [
|     'private_key' => env('POINTS_PRIVATE_KEY'),
|     'public_key' => env('POINTS_PUBLIC_KEY'),
|     'base_url' => env('POINTS_BASE_URL', 'https://api.papp.sa'),
| ],
|
| Then call the SDK from a controller or service:
|
| $points = app(\Papp\Points\Client::class);
| $order = $points->orders()->get($uuid);
|
*/

echo "See inline comments for Laravel integration.\n";
