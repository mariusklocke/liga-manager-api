<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use ArrayObject;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use HexagonalPlayground\Infrastructure\API\Application;
use HexagonalPlayground\Tests\Framework\GraphQL\Client;
use HexagonalPlayground\Tests\Framework\GraphQL\Exception;
use HexagonalPlayground\Tests\Framework\SlimClient;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Client $client;
    protected SlimClient $slimClient;
    private static ?Application $app = null;

    protected function setUp(): void
    {
        if (null === self::$app) {
            self::$app = new Application();
        }
        $this->slimClient = new SlimClient(self::$app);
        $this->client = new Client($this->slimClient);
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
        $token = $this->createAdminToken();
        $this->client->useToken($token);
    }

    protected function createAdminToken(): string
    {
        $this->client->useCredentials(getenv('ADMIN_EMAIL'), getenv('ADMIN_PASSWORD'));

        return $this->client->createToken();
    }

    protected function expectClientException(): void
    {
        self::expectException(Exception::class);
    }

    protected static function assertSimilarFloats(float $expected, float $actual): void
    {
        $tolerance = 0.00001;
        self::assertLessThan($tolerance, abs($expected - $actual));
    }

    protected static function assertArrayContainsObjectWithAttribute(array $array, string $attribute, $value): void
    {
        $filtered = array_filter($array, function ($object) use ($attribute, $value) {
            return is_object($object) && isset($object->$attribute) && $object->$attribute === $value;
        });
        self::assertGreaterThan(0, count($filtered));
    }

    protected static function parseDateTime(mixed $value): DateTime
    {
        self::assertIsString($value);
        $dateTime = DateTime::createFromFormat(DATE_ATOM, $value);
        self::assertInstanceOf(DateTime::class, $dateTime, "Invalid date format: $value");
        return $dateTime;
    }

    protected static function formatDateTime(DateTimeInterface $value): string
    {
        return $value->format(DATE_ATOM);
    }

    protected static function assertTimeZoneUsesDaylightSavingTime(DateTimeZone $timeZone): void
    {
        $from = new DateTimeImmutable('now', $timeZone);
        for ($i = 1; $i <= 365; $i++) {
            $day = $from->modify('+' . $i . ' day');
            if ($day->getOffset() !== $from->getOffset()) {
                return;
            }
        }
        self::fail('Failed to assert that ' . $timeZone->getName() . ' uses daylight saving time');
    }
}
