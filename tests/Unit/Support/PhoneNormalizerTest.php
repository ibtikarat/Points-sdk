<?php

declare(strict_types=1);

namespace Papp\Points\Tests\Unit\Support;

use InvalidArgumentException;
use Papp\Points\Support\PhoneNormalizer;
use PHPUnit\Framework\TestCase;

final class PhoneNormalizerTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function validCases(): array
    {
        return [
            'plus country code' => ['+966501234567', '501234567'],
            '00 country code' => ['00966501234567', '501234567'],
            'no plus country code' => ['966501234567', '501234567'],
            'leading zero' => ['0501234567', '501234567'],
            'bare 9 digits' => ['501234567', '501234567'],
            'with spaces' => ['+966 50 123 4567', '501234567'],
            'with dashes' => ['+966-50-123-4567', '501234567'],
            'mixed spaces dashes' => [' +966 50-123 4567 ', '501234567'],
        ];
    }

    /**
     * @dataProvider validCases
     */
    public function test_normalize_valid(string $input, string $expected): void
    {
        self::assertSame($expected, PhoneNormalizer::normalize($input));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidCases(): array
    {
        return [
            'too short' => ['5012345'],
            'starts with 4' => ['401234567'],
            'letters' => ['50abcdefg'],
            'empty' => [''],
            'too long' => ['5012345678910'],
        ];
    }

    /**
     * @dataProvider invalidCases
     */
    public function test_normalize_invalid(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        PhoneNormalizer::normalize($input);
    }

    public function test_is_valid(): void
    {
        self::assertTrue(PhoneNormalizer::isValid('+966501234567'));
        self::assertFalse(PhoneNormalizer::isValid('123'));
    }
}
