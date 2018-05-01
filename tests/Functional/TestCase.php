<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

use HexagonalPlayground\Application\EventStoreInterface;
use HexagonalPlayground\Infrastructure\API\Bootstrap;
use HexagonalPlayground\Tests\Functional\Framework\HttpClient;
use HexagonalPlayground\Tests\Functional\Framework\RichClient;
use Slim\App;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var RichClient */
    protected $client;

    /** @var App */
    private static $app;

    public function setUp()
    {
        $this->client = new RichClient(new HttpClient(self::getApp()));
        $this->getEventStore()->clear();
    }

    /**
     * @return App
     */
    private static function getApp(): App
    {
        if (null === self::$app) {
            self::$app = Bootstrap::bootstrap();
        }

        return self::$app;
    }

    /**
     * @return EventStoreInterface
     */
    protected function getEventStore(): EventStoreInterface
    {
        return self::getApp()->getContainer()->get(EventStoreInterface::class);
    }
}
