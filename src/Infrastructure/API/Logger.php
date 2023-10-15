<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use InvalidArgumentException;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class Logger extends AbstractLogger
{
    /** @var resource */
    private $stream;

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
     * @param resource $stream
     * @param string $minLevel
     */
    public function __construct($stream, string $minLevel)
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Invalid argument: stream is not a resource');
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
    public function log($level, $message, array $context = array())
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
        $line .= PHP_EOL;

        fwrite($this->stream, $line);
    }
}
