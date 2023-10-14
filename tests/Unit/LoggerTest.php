<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Infrastructure\Logger;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LoggerTest extends TestCase
{
    /** @var resource */
    private $stream;

    protected function setUp(): void
    {
        $this->stream = fopen('php://temp', 'w+');
    }

    public function testLoggerRespectsMinLevel(): void
    {
        $message = 'This is a log message';
        $logger = new Logger($this->stream, 'info');

        $logger->emergency($message);
        $logger->alert($message);
        $logger->critical($message);
        $logger->error($message);
        $logger->warning($message);
        $logger->notice($message);
        $logger->info($message);
        $logger->debug($message);

        rewind($this->stream);
        $output = stream_get_contents($this->stream);
        $expectedLevels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO
        ];
        foreach ($expectedLevels as $expectedLevel) {
            $expectedLevel = strtoupper($expectedLevel);
            self::assertStringContainsString("$expectedLevel: $message", $output);
        }
        self::assertStringNotContainsString('DEBUG', $output);
    }

    public function testInitiatingWithInvalidStreamFails(): void
    {
        self::expectException(InvalidArgumentException::class);
        new Logger('/just/a/path', 'debug');
    }

    public function testInitiatingWithInvalidMinLevelFails(): void
    {
        self::expectException(InvalidArgumentException::class);
        new Logger($this->stream, 'invalid');
    }
}
