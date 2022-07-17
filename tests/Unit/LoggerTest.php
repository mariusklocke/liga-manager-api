<?php

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
        Logger::init($this->stream, 'info');

        $logger = Logger::getInstance();
        $logger->emergency($message);
        $logger->alert($message);
        $logger->critical($message);
        $logger->error($message);
        $logger->warning($message);
        $logger->notice($message);
        $logger->info($message);
        $logger->debug($message);

        $output = $this->getStreamContent();
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

    public function testMessagePlaceholderAreResolved(): void
    {
        Logger::init($this->stream, 'info');

        $logger = Logger::getInstance();
        $logger->error('Encountered {count} errors', ['count' => 5]);
        $output = $this->getStreamContent();
        self::assertStringContainsString('ERROR: Encountered 5 errors', $output);
    }

    public function testInitiatingWithInvalidStreamFails(): void
    {
        self::expectException(InvalidArgumentException::class);
        Logger::init('/just/a/path', 'debug');
    }

    public function testInitiatingWithInvalidMinLevelFails(): void
    {
        self::expectException(InvalidArgumentException::class);
        Logger::init($this->stream, 'invalid');
    }

    private function getStreamContent(): string
    {
        $data = '';

        rewind($this->stream);
        while (!feof($this->stream)) {
            $data .= fread($this->stream, 4096);
        }

        return $data;
    }
}
