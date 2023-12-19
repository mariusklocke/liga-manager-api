<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Infrastructure\API\Logger;
use InvalidArgumentException;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LoggerTest extends TestCase
{
    public function testLoggerRespectsMinLevel(): void
    {
        $stream = new Stream(fopen('php://temp', 'w+'));
        $message = 'This is a log message';
        $logger = new Logger($stream, 'info');

        $logger->emergency($message);
        $logger->alert($message);
        $logger->critical($message);
        $logger->error($message);
        $logger->warning($message);
        $logger->notice($message);
        $logger->info($message);
        $logger->debug($message);

        $stream->rewind();
        $output = $stream->getContents();
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

    public function testInitiatingWithNonWritableStreamFails(): void
    {
        $stream = new Stream(fopen('php://temp', 'r'));
        self::expectException(InvalidArgumentException::class);
        new Logger($stream, 'debug');
    }

    public function testInitiatingWithInvalidMinLevelFails(): void
    {
        $stream = new Stream(fopen('php://temp', 'w+'));
        self::expectException(InvalidArgumentException::class);
        new Logger($stream, 'invalid');
    }
}
