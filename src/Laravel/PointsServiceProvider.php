<?php

declare(strict_types=1);

namespace PointsApp\Points\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use PointsApp\Points\Client;
use PointsApp\Points\Config;

/**
 * Registers the Points SDK in Laravel containers.
 *
 * Configure via `config/points.php` or these env vars:
 *   POINTS_PRIVATE_KEY
 *   POINTS_PUBLIC_KEY   (optional, only needed for checkout)
 *   POINTS_BASE_URL
 *   POINTS_TIMEOUT      (default 30)
 *   POINTS_RETRIES      (default 3)
 */
final class PointsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'points');

        $this->app->singleton(Client::class, static function (Application $app): Client {
            /** @var array<string, mixed> $config */
            $config = (array) $app['config']->get('points', []);

            return new Client(new Config([
                'private_key' => (string) ($config['private_key'] ?? ''),
                'public_key' => $config['public_key'] ?? null,
                'base_url' => (string) ($config['base_url'] ?? ''),
                'timeout' => (int) ($config['timeout'] ?? 30),
                'retries' => (int) ($config['retries'] ?? 3),
            ]));
        });

        $this->app->alias(Client::class, 'points');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->configPath() => $this->app->configPath('points.php'),
            ], 'points-config');
        }
    }

    private function configPath(): string
    {
        return __DIR__ . '/config/points.php';
    }
}
