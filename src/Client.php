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
 * Example:
 *   $points = new \PointsApp\Points\Client([
 *       'private_key' => 'points_private_key_xxx',
 *       'base_url'    => 'https://business.papp.sa',
 *   ]);
 *   $order = $points->orders()->createEarning([...]);
 */
final class Client
{
    private readonly Config $config;
    private readonly HttpClient $http;

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
     * }|Config $config
     */
    public function __construct(
        array|Config $config,
        ?ClientInterface $httpClient = null,
    ) {
        $configObj = $config instanceof Config ? $config : new Config($config);
        $this->config = $configObj;
        $this->http = new HttpClient($this->config, $httpClient);
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function http(): HttpClient
    {
        return $this->http;
    }

    public function orders(): Orders
    {
        return $this->orders ??= new Orders($this->http, $this->config);
    }

    public function webhooks(): Webhooks
    {
        return $this->webhooks ??= new Webhooks($this->http);
    }
}
