<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Infrastructure\API\Bootstrap;
use HexagonalPlayground\Infrastructure\Email\SwiftMailer;
use HexagonalPlayground\Tests\Framework\EmailClientInterface;
use HexagonalPlayground\Tests\Framework\Fixtures;
use HexagonalPlayground\Tests\Framework\GraphQL\Client;
use HexagonalPlayground\Tests\Framework\GraphQL\Exception;
use HexagonalPlayground\Tests\Framework\SlimClient;
use HexagonalPlayground\Tests\Framework\SwiftClient;
use Slim\App;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    protected $client;

    /** @var App */
    private static $app;

    /** @var EmailClientInterface */
    private static $emailClient;

    public static function setUpBeforeClass(): void
    {
        if (null === self::$app) {
            self::$app = Bootstrap::bootstrap();
        }
    }

    protected function setUp(): void
    {
        $this->client = new Client(new SlimClient(self::$app));
    }

    protected static function getEmailClient(): EmailClientInterface
    {
        if (null === self::$emailClient) {
            /** @var MailerInterface $mailer */
            $mailer = self::$app->getContainer()->get(MailerInterface::class);
            if (!($mailer instanceof SwiftMailer)) {
                throw new \Exception('Mailer has to be instance of SwiftMailer');
            }
            self::$emailClient = new SwiftClient($mailer);
        }
        return self::$emailClient;
    }

    protected function useAdminAuth(): void
    {
        $this->client->useCredentials(Fixtures::ADMIN_USER_EMAIL, Fixtures::ADMIN_USER_PASSWORD);
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