<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use PointsApp\Points\Client;

$config = [
    'private_key' => getenv('POINTS_PRIVATE_KEY') ?: 'points_private_key_xxx',
    'base_url' => getenv('POINTS_BASE_URL') ?: 'https://business.papp.sa',
];

$orderUuid = getenv('POINTS_ORDER_UUID') ?: '550e8400-e29b-41d4-a716-446655440000';
$dryRun = getenv('POINTS_DRY_RUN') !== '0';

if ($dryRun) {
    echo "Dry run enabled.\n";
    echo "Would authorize order {$orderUuid} against {$config['base_url']}.\n";
    exit(0);
}

$client = new Client($config);
$order = $client->orders()->authorize($orderUuid);

echo "Authorized order {$order->uuid} with status {$order->orderStatus->value}.\n";
