<?php

declare(strict_types=1);

namespace PointsApp\Points\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Base exception for all SDK errors. Exposes the raw API payload when available.
 */
class PointsException extends RuntimeException
{
    /** @var array<string, mixed> */
    protected array $payload;

    protected ?int $statusCode;

    protected ?string $requestId;

    /**
     * @param array<string, mixed> $payload raw decoded response body
     */
    public function __construct(
        string $message,
        ?int $statusCode = null,
        array $payload = [],
        ?string $requestId = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode ?? 0, $previous);
        $this->statusCode = $statusCode;
        $this->payload = $payload;
        $this->requestId = $requestId;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}
