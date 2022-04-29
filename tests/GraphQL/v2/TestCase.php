<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use DateTimeInterface;
use HexagonalPlayground\Infrastructure\Environment;
use HexagonalPlayground\Tests\Framework\GraphQL\AdvancedClient;
use HexagonalPlayground\Tests\Framework\GraphQL\BasicAuth;
use HexagonalPlayground\Tests\Framework\GraphQL\BearerAuth;
use HexagonalPlayground\Tests\Framework\GraphQL\Exception as ClientException;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected BearerAuth $defaultAdminAuth;

    protected static ?AdvancedClient $client = null;

    protected function setUp(): void
    {
        if (null === self::$client) {
            self::$client = new AdvancedClient();
        }
        $this->defaultAdminAuth = self::$client->authenticate(new BasicAuth(
            Environment::get('ADMIN_EMAIL'),
            Environment::get('ADMIN_PASSWORD')
        ));
    }

    protected static function formatDateTime(DateTimeInterface $dateTime): string
    {
        return $dateTime->format(DATE_ATOM);
    }

    protected static function formatDate(DateTimeInterface $dateTime): string
    {
        return $dateTime->format('Y-m-d');
    }

    protected function expectClientException(): void
    {
        $this->expectException(ClientException::class);
    }

    protected static function assertSimilarFloats(float $expected, float $actual, float $tolerance = 0.00001): void
    {
        self::assertLessThan($tolerance, abs($expected - $actual));
    }

    protected static function assertArraysHaveEqualValues(array $a, array $b): void
    {
        self::assertCount(0, array_diff($a, $b));
        self::assertCount(0, array_diff($b, $a));
    }
}
