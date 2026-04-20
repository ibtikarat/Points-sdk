<?php

declare(strict_types=1);

namespace PointsApp\Points\Http;

/**
 * Parsed, strongly-typed wrapper for the Points API response envelope:
 *   { "status": bool, "message": string, "appended_data": object, "data": mixed, ... }
 */
final class Response
{
    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $body    decoded JSON body (envelope)
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly array $headers,
        public readonly array $body,
        public readonly string $rawBody,
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function envelopeStatus(): bool
    {
        return (bool) ($this->body['status'] ?? $this->isSuccess());
    }

    public function message(): string
    {
        $msg = $this->body['message'] ?? '';

        return is_string($msg) ? $msg : '';
    }

    /**
     * Returns the inner "data" field of the response envelope, or the whole
     * body when no envelope is present (e.g. validation error responses).
     *
     * @return mixed
     */
    public function data(): mixed
    {
        if (array_key_exists('data', $this->body)) {
            return $this->body['data'];
        }

        return $this->body;
    }

    /**
     * @return array<string, mixed>
     */
    public function appendedData(): array
    {
        $appended = $this->body['appended_data'] ?? [];

        return is_array($appended) ? $appended : [];
    }

    public function header(string $name): ?string
    {
        $lower = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower((string) $key) === $lower) {
                if (is_array($value)) {
                    return isset($value[0]) ? (string) $value[0] : null;
                }

                return (string) $value;
            }
        }

        return null;
    }
}
