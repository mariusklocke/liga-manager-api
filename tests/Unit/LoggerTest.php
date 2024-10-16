<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Infrastructure\API\Logger;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LoggerTest extends TestCase
{
    private string $filePath;

    protected function setUp(): void
    {
        $this->filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('phpunit', true);
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }

    public function testLoggerRespectsMinLevel(): void
    {
        $message = 'This is a log message';
        $logger = new Logger($this->filePath, 'info');

        $logger->emergency($message);
        $logger->alert($message);
        $logger->critical($message);
        $logger->error($message);
        $logger->warning($message);
        $logger->notice($message);
        $logger->info($message);
        $logger->debug($message);

        $output = file_get_contents($this->filePath);
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

    public function testInitiatingWithInvalidMinLevelFails(): void
    {
        self::expectException(InvalidArgumentException::class);
        new Logger($this->filePath, 'invalid');
    }
}
