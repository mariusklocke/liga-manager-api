<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\MultipartStream;
use HexagonalPlayground\Infrastructure\API\Application;
use HexagonalPlayground\Tests\Framework\GraphQL\Client;
use HexagonalPlayground\Tests\Framework\GraphQL\Exception;
use HexagonalPlayground\Tests\Framework\MaildevClient;
use HexagonalPlayground\Tests\Framework\OpenApiValidator;
use HexagonalPlayground\Tests\Framework\PsrSlimClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Client $client;
    protected ClientInterface $psrClient;
    protected MaildevClient $mailClient;
    private ServerRequestFactoryInterface $requestFactory;
    private UploadedFileFactoryInterface $uploadedFileFactory;
    private StreamFactoryInterface $streamFactory;
    private OpenApiValidator $schemaValidator;
    private static ?Application $app = null;

    protected function setUp(): void
    {
        if (!extension_loaded('xdebug')) {
            $httpFactory = new HttpFactory();
            $this->requestFactory = $httpFactory;
            $this->uploadedFileFactory = $httpFactory;
            $this->streamFactory = $httpFactory;
            $this->psrClient = new GuzzleClient(['base_uri' => getenv('APP_BASE_URL')]);
        } else {
            $psr17Factory = new Psr17Factory();
            $this->requestFactory = $psr17Factory;
            $this->uploadedFileFactory = $psr17Factory;
            $this->streamFactory = $psr17Factory;
            if (null === self::$app) {
                self::$app = new Application();
            }
            $this->psrClient = new PsrSlimClient(self::$app);
        }
        $this->client = new Client($this->psrClient, $this->requestFactory);
        $this->mailClient = new MaildevClient();
        $this->schemaValidator = new OpenApiValidator();
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
        $request  = $this->buildRequest($method, $uri, $headers);
        $stream   = $this->streamFactory->createStreamFromFile($filePath);
        $fileSize = filesize($filePath);
        $fileName = basename($filePath);

        if (extension_loaded('xdebug')) {
            $uploadedFile = $this->uploadedFileFactory->createUploadedFile($stream, $fileSize, 0, $fileName, $fileMediaType);
            $request = $request->withUploadedFiles(['file' => $uploadedFile]);
        } else {
            $boundary = 'boundary_' . uniqid();
            $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . $boundary);

            // Build the multipart form data using a stream
            $multipart = new MultipartStream([
                [
                    'name'     => 'file',
                    'filename' => $fileName,
                    'contents' => $stream,
                    'headers'  => [
                        'Content-Type' => $fileMediaType,
                        'Content-Length' => (string)$fileSize,
                    ]
                ]
            ], $boundary);

            $request = $request->withBody($multipart);
        }

        return $request;
    }

    /**
     * Sends a request to the application and validates the response against the OpenAPI schema.
     * 
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function sendRequest(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->psrClient->sendRequest($request);

        $this->schemaValidator->validateResponse($request, $response);

        return $response;
    }
}
