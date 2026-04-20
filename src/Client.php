<?php

declare(strict_types=1);

namespace PointsApp\Points;

use GuzzleHttp\ClientInterface;
use PointsApp\Points\Http\HttpClient;
use PointsApp\Points\Resources\Orders;
use PointsApp\Points\Resources\Webhooks;

/**
 * Entry point for the Points SDK.
 *
 * Can be instantiated without config and configured later:
 *   $points = new \PointsApp\Points\Client();
 *   $points->configure(['private_key' => '...', 'base_url' => 'https://business.papp.sa']);
 *
 * Or configured up front:
 *   $points = new \PointsApp\Points\Client([
 *       'private_key' => 'points_private_key_xxx',
 *       'base_url'    => 'https://business.papp.sa',
 *   ]);
 */
final class Client
{
    private ?Config $config = null;
    private ?HttpClient $http = null;

    private ?Orders $orders = null;
    private ?Webhooks $webhooks = null;

    /**
     * @param array{
     *     private_key: string,
     *     base_url: string,
     *     public_key?: string|null,
     *     timeout?: int,
     *     connect_timeout?: int,
     *     retries?: int,
     *     logger?: \Psr\Log\LoggerInterface|null,
     * }|Config|null $config
     */
    public function __construct(
        array|Config|null $config = null,
        ?ClientInterface $httpClient = null,
    ) {
        if ($config !== null) {
            $this->boot($config, $httpClient);
        }
    }

    /**
     * @param array{
     *     private_key: string,
     *     base_url: string,
     *     public_key?: string|null,
     *     timeout?: int,
     *     connect_timeout?: int,
     *     retries?: int,
     *     logger?: \Psr\Log\LoggerInterface|null,
     * }|Config $config
     */
    public function configure(array|Config $config, ?ClientInterface $httpClient = null): static
    {
        $this->orders = null;
        $this->webhooks = null;
        $this->boot($config, $httpClient);

        return $this;
    }

    public function config(): Config
    {
        return $this->resolvedConfig();
    }

    public function http(): HttpClient
    {
        return $this->resolvedHttp();
    }

    public function orders(): Orders
    {
        return $this->orders ??= new Orders($this->resolvedHttp(), $this->resolvedConfig());
    }

    public function webhooks(): Webhooks
    {
        return $this->webhooks ??= new Webhooks($this->resolvedHttp());
    }

    /**
     * @param array<string, mixed>|Config $config
     */
    private function boot(array|Config $config, ?ClientInterface $httpClient): void
    {
        $this->config = $config instanceof Config ? $config : new Config($config);
        $this->http = new HttpClient($this->config, $httpClient);
    }

    private function resolvedConfig(): Config
    {
        if ($this->config === null) {
            throw new \RuntimeException('Points client is not configured. Call configure() or pass config to the constructor.');
        }

        return $this->config;
    }

    private function resolvedHttp(): HttpClient
    {
        if ($this->http === null) {
            throw new \RuntimeException('Points client is not configured. Call configure() or pass config to the constructor.');
        }

        return $this->http;
    }
}
