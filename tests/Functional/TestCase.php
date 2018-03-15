<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

use HexagonalPlayground\Infrastructure\API\Bootstrap;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    private static $client;

    /**
     * @return Client
     */
    protected static function getClient() : Client
    {
        if (null === self::$client) {
            self::$client = new Client(Bootstrap::bootstrap());
        }

        return self::$client;
    }
}
