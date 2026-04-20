<?php

declare(strict_types=1);

return [
    'private_key' => env('POINTS_PRIVATE_KEY'),
    'public_key' => env('POINTS_PUBLIC_KEY'),
    'base_url' => env('POINTS_BASE_URL', 'https://api.papp.sa'),
    'timeout' => (int) env('POINTS_TIMEOUT', 30),
    'retries' => (int) env('POINTS_RETRIES', 3),
];
