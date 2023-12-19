<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class Logger extends AbstractLogger
{
    private StreamInterface $stream;

    /** @var string */
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
     * @param StreamInterface $stream
     * @param string $minLevel
     */
    public function __construct(StreamInterface $stream, string $minLevel)
    {
        if (!$stream->isWritable()) {
            throw new InvalidArgumentException('Invalid argument: stream is not writable');
        }

        if (!array_key_exists($minLevel, self::$severityMap)) {
            throw new InvalidArgumentException('Invalid argument: minLevel is not a valid log level');
        }

        $this->stream = $stream;
        $this->minLevel = $minLevel;
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = array()): void
    {
        if (self::$severityMap[$level] < self::$severityMap[$this->minLevel]) {
            return;
        }
        if (!$this->stream->isWritable()) {
            return; // Workaround for Doctrine DBAL logging issues on application shutdown
        }

        $timestamp = date('Y-m-d H:i:s P');
        $level = strtoupper($level);
        $line = "[$timestamp] $level: $message";
        if (count($context)) {
            $line .= ' ' . json_encode($context);
        }
        $line .= PHP_EOL;

        $this->stream->write($line);
    }
}
