<?php

declare(strict_types=1);

namespace PointsApp\Points;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Immutable configuration for the Points SDK client.
 */
final class Config
{
    public readonly string $privateKey;
    public readonly ?string $publicKey;
    public readonly string $baseUrl;
    public readonly int $timeout;
    public readonly int $connectTimeout;
    public readonly int $retries;
    public readonly LoggerInterface $logger;

    /**
     * @param array{
     *     private_key: string,
     *     base_url: string,
     *     public_key?: string|null,
     *     timeout?: int,
     *     connect_timeout?: int,
     *     retries?: int,
     *     logger?: LoggerInterface|null,
     * } $options
     */
    public function __construct(array $options)
    {
        if (empty($options['private_key']) || !is_string($options['private_key'])) {
            throw new InvalidArgumentException('Config: "private_key" is required and must be a non-empty string.');
        }

        if (empty($options['base_url']) || !is_string($options['base_url'])) {
            throw new InvalidArgumentException('Config: "base_url" is required and must be a non-empty string (e.g. https://business.papp.sa).');
        }

        $publicKey = $options['public_key'] ?? null;
        if ($publicKey !== null && !is_string($publicKey)) {
            throw new InvalidArgumentException('Config: "public_key" must be a string when provided.');
        }

        $this->privateKey = $options['private_key'];
        $this->publicKey = $publicKey !== null && $publicKey !== '' ? $publicKey : null;
        $this->baseUrl = rtrim($options['base_url'], '/');
        $this->timeout = (int) ($options['timeout'] ?? 30);
        $this->connectTimeout = (int) ($options['connect_timeout'] ?? 10);
        $this->retries = (int) ($options['retries'] ?? 3);
        $this->logger = $options['logger'] ?? new NullLogger();

        if ($this->timeout < 1) {
            throw new InvalidArgumentException('Config: "timeout" must be >= 1 second.');
        }
        if ($this->retries < 0) {
            throw new InvalidArgumentException('Config: "retries" must be >= 0.');
        }
    }
}
