<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use Doctrine\ORM\EntityManager;
use HexagonalPlayground\Infrastructure\API\Bootstrap;
use HexagonalPlayground\Tests\Framework\Fixtures;
use HexagonalPlayground\Tests\Framework\GraphQL\Client;
use HexagonalPlayground\Tests\Framework\GraphQL\Exception;
use HexagonalPlayground\Tests\Framework\SlimClient;
use Slim\App;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    protected $client;

    /** @var App */
    private static $app;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    protected function tearDown(): void
    {
        /** @var EntityManager $em */
        $em = self::$app->getContainer()->get(EntityManager::class);
        $em->getConnection()->close();
    }

    protected function useAdminAuth(): void
    {
        $this->client->useCredentials(Fixtures::ADMIN_USER_EMAIL, Fixtures::ADMIN_USER_PASSWORD);
    }

    protected function expectClientException(): void
    {
        self::expectException(Exception::class);
    }

    protected static function createClient(): Client
    {
        self::$app = Bootstrap::bootstrap();
        return new Client(new SlimClient(self::$app));
    }

    protected static function assertSimilarFloats(float $expected, float $actual)
    {
        $tolerance = 0.00001;
        self::assertLessThan($tolerance, abs($expected - $actual));
    }

    protected static function assertArrayContainsObjectWithAttribute(array $array, string $attribute, $value)
    {
        $filtered = array_filter($array, function ($object) use ($attribute, $value) {
            return is_object($object) && isset($object->$attribute) && $object->$attribute === $value;
        });
        self::assertGreaterThan(0, count($filtered));
    }
}