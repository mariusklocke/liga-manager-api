<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Infrastructure\API\Bootstrap;
use HexagonalPlayground\Tests\Framework\GraphQL\Client;
use HexagonalPlayground\Tests\Framework\SlimClient;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    protected $client;

    protected function setUp(): void
    {
        $this->client = new Client(new SlimClient(Bootstrap::bootstrap()));
    }

    protected static function assertSimilarFloats(float $expected, float $actual)
    {
        $tolerance = 0.00001;
        self::assertLessThan($tolerance, abs($expected - $actual));
    }
}