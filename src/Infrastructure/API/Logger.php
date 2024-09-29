<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Stringable;

class Logger extends AbstractLogger
{
    private StreamInterface|null $stream;
    private string $minLevel;
    private static array $severityMap = [
        LogLevel::EMERGENCY => 7,
        LogLevel::ALERT => 6,
        LogLevel::CRITICAL => 5,
        LogLevel::ERROR => 4,
        LogLevel::WARNING => 3,
        LogLevel::NOTICE => 2,
        LogLevel::INFO => 1,
        LogLevel::DEBUG => 0
    ];

    /**
     * @param string $filePath
     * @param string $minLevel
     */
    public function __construct(string $filePath, string $minLevel)
    {
        if ($filePath !== '') {
            $this->stream = (new FilesystemService())->openFile($filePath, 'a');
        } else {
            $this->stream = null;
        }

        if (!array_key_exists($minLevel, self::$severityMap)) {
            throw new InvalidArgumentException('Invalid argument: minLevel is not a valid log level');
        }

        $this->minLevel = $minLevel;
    }

    /**
     * @param string $level
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function log($level, string|Stringable $message, array $context = array()): void
    {
        if (self::$severityMap[$level] < self::$severityMap[$this->minLevel]) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s P');
        $level = strtoupper($level);
        $line = "[$timestamp] $level: $message";
        if (count($context)) {
            $line .= ' ' . json_encode($context);
        }

        if ($this->stream !== null && $this->stream->isWritable()) {
            $this->stream->write($line . PHP_EOL);
        } else {
            error_log($line);
        }

    }
}
