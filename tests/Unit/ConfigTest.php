<?php

declare(strict_types=1);

namespace PointsApp\Points\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PointsApp\Points\Config;

final class ConfigTest extends TestCase
{
    public function test_requires_private_key(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config(['base_url' => 'https://api.papp.sa']);
    }

    public function test_requires_base_url(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config(['private_key' => 'x']);
    }

    public function test_trims_trailing_slash_on_base_url(): void
    {
        $config = new Config([
            'private_key' => 'k',
            'base_url' => 'https://api.papp.sa/',
        ]);

        self::assertSame('https://api.papp.sa', $config->baseUrl);
    }

    public function test_defaults(): void
    {
        $config = new Config([
            'private_key' => 'k',
            'base_url' => 'https://api.papp.sa',
        ]);

        self::assertSame(30, $config->timeout);
        self::assertSame(3, $config->retries);
        self::assertNull($config->publicKey);
    }

    public function test_coerces_empty_public_key_to_null(): void
    {
        $config = new Config([
            'private_key' => 'k',
            'public_key' => '',
            'base_url' => 'https://api.papp.sa',
        ]);

        self::assertNull($config->publicKey);
    }

    public function test_rejects_negative_retries(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config([
            'private_key' => 'k',
            'base_url' => 'https://api.papp.sa',
            'retries' => -1,
        ]);
    }
}
