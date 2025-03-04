<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Infrastructure\API\Logger;
use HexagonalPlayground\Infrastructure\Config;
use HexagonalPlayground\Tests\Framework\File;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LoggerTest extends TestCase
{
    private File $logFile;

    protected function setUp(): void
    {
        $this->logFile = File::temp('logger_test_', '.log');
    }

    protected function tearDown(): void
    {
        $this->logFile->delete();
    }

    public function testLoggerRespectsMinLevel(): void
    {
        $config = new Config([
            'log.path' => $this->logFile->getPath(),
            'log.level' => 'info'
        ]);
        $logger = new Logger($config);
        $message = 'This is a log message';

        $logger->emergency($message);
        $logger->alert($message);
        $logger->critical($message);
        $logger->error($message);
        $logger->warning($message);
        $logger->notice($message);
        $logger->info($message);
        $logger->debug($message);

        $output = $this->logFile->read();
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
        $config = new Config([
            'log.path' => $this->logFile->getPath(),
            'log.level' => 'invalid'
        ]);
        self::expectException(InvalidArgumentException::class);
        new Logger($config);
    }
}
