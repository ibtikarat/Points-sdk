<?php

declare(strict_types=1);

namespace PointsApp\Points\Exceptions;

/**
 * Thrown when the API key is missing, malformed or inactive.
 *
 * The backend returns HTTP 400 for authentication failures — we surface them
 * separately so merchants can distinguish auth errors from other client errors.
 */
final class AuthenticationException extends PointsException
{
}
