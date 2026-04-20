<?php

declare(strict_types=1);

namespace Papp\Points\Exceptions;

use Throwable;

/**
 * Thrown on HTTP 422 validation failures.
 */
final class ValidationException extends PointsException
{
    /** @var array<string, array<int, string>> */
    private array $errors;

    /**
     * @param array<string, mixed>              $payload
     * @param array<string, array<int, string>> $errors  field -> list of messages
     */
    public function __construct(
        string $message,
        array $errors = [],
        array $payload = [],
        ?int $statusCode = 422,
        ?string $requestId = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $payload, $requestId, $previous);
        $this->errors = $errors;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
