<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use ArrayObject;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use GuzzleHttp\Client as GuzzleClient;
use HexagonalPlayground\Infrastructure\API\Application;
use HexagonalPlayground\Tests\Framework\GraphQL\Client;
use HexagonalPlayground\Tests\Framework\GraphQL\Exception;
use HexagonalPlayground\Tests\Framework\PsrSlimClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Client $client;
    protected ClientInterface $psrClient;
    private ServerRequestFactoryInterface $requestFactory;
    private UploadedFileFactoryInterface $uploadedFileFactory;
    private StreamFactoryInterface $streamFactory;
    private static ?Application $app = null;

    protected function setUp(): void
    {
        if (!extension_loaded('xdebug')) {
            $this->psrClient = new GuzzleClient(['base_uri' => getenv('APP_BASE_URL')]);
        } else {
            if (null === self::$app) {
                self::$app = new Application();
            }
            $this->psrClient = new PsrSlimClient(self::$app);
        }
        $psr17Factory = new Psr17Factory();
        $this->requestFactory = $psr17Factory;
        $this->uploadedFileFactory = $psr17Factory;
        $this->streamFactory = $psr17Factory;
        $this->client = new Client($this->psrClient, $this->requestFactory);
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

    protected function buildRequest(string $method, string $uri, array $headers = []): ServerRequestInterface
    {
        $request = $this->requestFactory->createServerRequest($method, $uri);

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        return $request;
    }

    protected function buildUploadRequest(string $method, string $uri, string $filePath, string $fileMediaType, array $headers = []): ServerRequestInterface
    {
        $file = $this->uploadedFileFactory->createUploadedFile(
            $this->streamFactory->createStreamFromFile($filePath),
            filesize($filePath),
            0,
            basename($filePath),
            $fileMediaType
        );

        $request = $this->requestFactory->createServerRequest($method, $uri);
        $request = $request->withUploadedFiles(['file' => $file]);

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        return $request;
    }
}
