<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Infrastructure\API\Bootstrap;
use HexagonalPlayground\Infrastructure\Email\SwiftMailer;
use HexagonalPlayground\Infrastructure\Environment;
use HexagonalPlayground\Tests\Framework\EmailListenerInterface;
use HexagonalPlayground\Tests\Framework\GraphQL\Client;
use HexagonalPlayground\Tests\Framework\GraphQL\Exception;
use HexagonalPlayground\Tests\Framework\SlimClient;
use HexagonalPlayground\Tests\Framework\SwiftMailListener;
use Nyholm\Psr7\Factory\Psr17Factory;
use Slim\App;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    protected $client;

    /** @var App */
    private static $app;

    /** @var EmailListenerInterface */
    private static $emailListener;

    protected function setUp(): void
    {
        if (null === self::$app) {
            self::$app = Bootstrap::bootstrap();
        }
        $this->client = new Client(new SlimClient(self::$app, new Psr17Factory()));
    }

    protected static function getEmailListener(): EmailListenerInterface
    {
        if (null === self::$emailListener) {
            /** @var MailerInterface $mailer */
            $mailer = self::$app->getContainer()->get(MailerInterface::class);
            if (!($mailer instanceof SwiftMailer)) {
                throw new \Exception('Unsupported Mailer Type: ' . get_class($mailer));
            }
            self::$emailListener = new SwiftMailListener($mailer);
        }
        return self::$emailListener;
    }

    protected function useAdminAuth(): void
    {
        $this->client->useCredentials(Environment::get('ADMIN_EMAIL'), Environment::get('ADMIN_PASSWORD'));
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