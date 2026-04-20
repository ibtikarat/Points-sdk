<?php

declare(strict_types=1);

namespace Papp\Points\Exceptions;

/**
 * Thrown on 5xx responses. These are transient by nature — the HTTP client
 * already retries them with exponential backoff before giving up.
 */
final class ServerException extends PointsException
{
}
