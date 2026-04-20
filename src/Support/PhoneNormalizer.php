<?php

declare(strict_types=1);

namespace Papp\Points\Support;

use InvalidArgumentException;

/**
 * Mirrors the Saudi phone-number normalization logic in the Points backend.
 *
 * Accepts any of these formats and returns the canonical 9-digit form:
 *   +966501234567, 00966501234567, 966501234567, 0501234567, 501234567
 *   (spaces/dashes permitted, e.g. "+966 50-123 4567")
 *
 * Canonical output: 9 digits starting with "5" (e.g. "501234567").
 */
final class PhoneNormalizer
{
    public static function normalize(string $phone): string
    {
        $cleaned = preg_replace('/[\s\-]/', '', $phone) ?? $phone;
        $cleaned = preg_replace('/^(?:\+966|00966|966|0)/', '', $cleaned) ?? $cleaned;

        if (!preg_match('/^5\d{8}$/', $cleaned)) {
            throw new InvalidArgumentException(
                sprintf('Invalid Saudi phone number "%s": expected 9 digits starting with 5 after stripping country code.', $phone),
            );
        }

        return $cleaned;
    }

    public static function isValid(string $phone): bool
    {
        try {
            self::normalize($phone);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
