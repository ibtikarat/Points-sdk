# Points PHP SDK

Official PHP SDK for the Points loyalty platform at [papp.sa](https://papp.sa).

## Installation

```bash
composer require papp/points-sdk
```

## Quick Start

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Papp\Points\Client;
use Papp\Points\Enums\PaymentMethod;

$points = new Client([
    'private_key' => 'points_private_key_xxx',
    'public_key' => 'points_public_key_xxx',
    'base_url' => 'https://api.papp.sa',
    'timeout' => 30,
    'retries' => 3,
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
- `base_url` is required so you can target production or sandbox explicitly.
- `public_key` is optional and only used for checkout endpoints.
- `timeout`, `connect_timeout`, and `retries` are configurable.

## Supported PHP Versions

PHP 8.1 or newer.

## Webhooks

The backend sends the webhook secret in the `X-Webhook-Secret` header. Verification is a direct shared-secret comparison, not an HMAC signature.

```php
use Papp\Points\Webhooks\WebhookHandler;

$handler = new WebhookHandler('your_webhook_secret');
$event = $handler->parse($rawPayload, $secretHeader, $eventHeader);
```

## Full Documentation

Full documentation will be published at [docs.papp.sa](https://docs.papp.sa).

## Contributing

1. Create a feature branch from `ai`.
2. Run `./vendor/bin/phpunit`.
3. Run `./vendor/bin/phpstan analyse`.
4. Run `./vendor/bin/php-cs-fixer fix --dry-run --diff`.

## License

MIT

