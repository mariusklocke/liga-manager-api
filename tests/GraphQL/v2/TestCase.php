<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use DateTimeInterface;
use HexagonalPlayground\Infrastructure\Environment;
use HexagonalPlayground\Tests\Framework\GraphQL\AdvancedClient;
use HexagonalPlayground\Tests\Framework\GraphQL\BasicAuth;
use stdClass;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected BasicAuth $defaultAdminAuth;

    protected static ?AdvancedClient $client = null;

    protected function setUp(): void
    {
        if (null === self::$client) {
            self::$client = new AdvancedClient();
        }
        $this->defaultAdminAuth = new BasicAuth(
            Environment::get('ADMIN_EMAIL'),
            Environment::get('ADMIN_PASSWORD')
        );
    }

    protected static function formatDate(DateTimeInterface $dateTime): string
    {
        return $dateTime->format(DATE_ATOM);
    }

    protected static function assertResponseNotHasError(stdClass $response): void
    {
        $hasErrors = isset($response->errors) && count($response->errors) > 0;
        $message   = $hasErrors ? json_encode($response->errors) : '';

        self::assertObjectNotHasAttribute('errors', $response, $message);
    }

    protected static function assertSimilarFloats(float $expected, float $actual, float $tolerance = 0.00001)
    {
        self::assertLessThan($tolerance, abs($expected - $actual));
    }
}
