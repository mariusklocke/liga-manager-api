<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use ArrayObject;
use HexagonalPlayground\Infrastructure\API\Application;
use HexagonalPlayground\Tests\Framework\GraphQL\Client;
use HexagonalPlayground\Tests\Framework\GraphQL\Exception;
use HexagonalPlayground\Tests\Framework\SlimClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Slim\App;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    protected $client;

    /** @var App */
    private static $app;

    protected function setUp(): void
    {
        if (null === self::$app) {
            self::$app = new Application();
        }
        $this->client = new Client(new SlimClient(self::$app, new Psr17Factory()));
    }

    /**
     * @param string $eventName
     * @param callable $callable
     * @return array
     */
    protected static function catchEvents(string $eventName, callable $callable): array
    {
        $events = new ArrayObject();

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = self::$app->getContainer()->get(EventDispatcherInterface::class);

        $listener = function ($event) use ($events) {
            $events[] = $event;
        };

        $eventDispatcher->addListener($eventName, $listener);
        $callable();
        $eventDispatcher->removeListener($eventName, $listener);

        return $events->getArrayCopy();
    }

    protected function useAdminAuth(): void
    {
        $this->client->useCredentials(getenv('ADMIN_EMAIL'), getenv('ADMIN_PASSWORD'));
        $token = $this->client->createToken();
        $this->client->useToken($token);
    }

    protected function expectClientException(): void
    {
        self::expectException(Exception::class);
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
