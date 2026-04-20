<?php

declare(strict_types=1);

namespace PointsApp\Points\Exceptions;

/**
 * Thrown by the webhook handler when the incoming secret does not match.
 */
final class InvalidSignatureException extends PointsException
{
}
