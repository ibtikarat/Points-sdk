<?php

declare(strict_types=1);

namespace PointsApp\Points\Exceptions;

use Throwable;

final class RateLimitException extends PointsException
{
    private ?int $retryAfter;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        string $message,
        ?int $retryAfter = null,
        array $payload = [],
        ?int $statusCode = 429,
        ?string $requestId = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $payload, $requestId, $previous);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
