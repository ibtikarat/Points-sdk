# Points PHP SDK

Official PHP SDK for the Points loyalty platform at [papp.sa](https://papp.sa).

## Installation

```bash
composer require points-app/points-sdk
```

## Environment Variables

Add the following to your `.env` file:

```env
# Required – your merchant private API key
POINTS_PRIVATE_KEY=points_private_key_xxx

# Optional – only needed for checkout (createCheckout) endpoints
POINTS_PUBLIC_KEY=points_public_key_xxx

# API base URL – use production or sandbox (see Environments below)
POINTS_BASE_URL=https://business.papp.sa

# Optional tuning
POINTS_TIMEOUT=30
POINTS_RETRIES=3
```

## Environments

| Environment | Base URL |
|-------------|----------|
| Production  | `https://business.papp.sa` |
| Sandbox / Testing | `https://sandbox.papp.sa` |

## Quick Start

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use PointsApp\Points\Client;
use PointsApp\Points\Enums\PaymentMethod;

$points = new Client([
    'private_key' => env('POINTS_PRIVATE_KEY'),
    'public_key'  => env('POINTS_PUBLIC_KEY'),
    'base_url'    => env('POINTS_BASE_URL', 'https://business.papp.sa'),
    'timeout'     => 30,
    'retries'     => 3,
]);

$checkoutUrl = $points->orders()->createCheckout([
    'phone_number' => '0555123456',
    'name' => 'Customer Name',
    'total_price' => 150.50,
    'order_number' => 'SHOP-1001',
    'products' => [
        [
            'product_name' => 'T-Shirt',
            'product_price' => 150.50,
            'quantity' => 1,
        ],
    ],
]);

$order = $points->orders()->authorize('550e8400-e29b-41d4-a716-446655440000');
$order = $points->orders()->capture($order->uuid);
$order = $points->orders()->complete($order->uuid, PaymentMethod::Visa);
```

## Configuration

- `private_key` is required and is sent as the `x-api-key` header.
- `base_url` is required — use `https://business.papp.sa` for production or `https://sandbox.papp.sa` for testing.
- `public_key` is optional and only used for checkout endpoints.
- `timeout`, `connect_timeout`, and `retries` are configurable.

## Supported PHP Versions

PHP 8.2, 8.3, 8.4, and 8.5.

## Webhooks

The backend sends the webhook secret in the `X-Webhook-Secret` header. Verification is a direct shared-secret comparison, not an HMAC signature.

```php
use PointsApp\Points\Webhooks\WebhookHandler;

$handler = new WebhookHandler('your_webhook_secret');
$event = $handler->parse($rawPayload, $secretHeader, $eventHeader);
```

## Full Documentation

Full documentation will be published at [docs.papp.sa](https://docs.papp.sa).

## Contributing

1. Create a feature branch from `main`.
2. Run `./vendor/bin/phpunit`.
3. Run `./vendor/bin/phpstan analyse`.
4. Run `./vendor/bin/php-cs-fixer fix --dry-run --diff`.

## License

MIT
