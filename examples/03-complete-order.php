<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use PointsApp\Points\Client;
use PointsApp\Points\Enums\PaymentMethod;

$config = [
    'private_key' => getenv('POINTS_PRIVATE_KEY') ?: 'points_private_key_xxx',
    'base_url' => getenv('POINTS_BASE_URL') ?: 'https://api.papp.sa',
];

$orderUuid = getenv('POINTS_ORDER_UUID') ?: '550e8400-e29b-41d4-a716-446655440000';
$dryRun = getenv('POINTS_DRY_RUN') !== '0';

if ($dryRun) {
    echo "Dry run enabled.\n";
    echo "Would complete order {$orderUuid} using payment method " . PaymentMethod::Visa->value . ".\n";
    exit(0);
}

$client = new Client($config);
$order = $client->orders()->complete($orderUuid, PaymentMethod::Visa);

echo "Completed order {$order->uuid} with status {$order->orderStatus->value}.\n";
