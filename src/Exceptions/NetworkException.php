<?php

declare(strict_types=1);

namespace Papp\Points\Exceptions;

/**
 * Thrown when the request never reached the server (DNS failure, connection
 * refused, timeout, TLS errors, etc.) after all retries.
 */
final class NetworkException extends PointsException
{
}
