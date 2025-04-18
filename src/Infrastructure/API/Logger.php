<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\Config;
use InvalidArgumentException;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Stringable;

class Logger extends AbstractLogger
{
    private mixed $stream;
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

    public function __construct(Config $config)
    {
        $filePath = $config->getValue('log.path', '');
        $minLevel = $config->getValue('log.level', LogLevel::DEBUG);

        if (!array_key_exists($minLevel, self::$severityMap)) {
            throw new InvalidArgumentException('Invalid argument: minLevel is not a valid log level');
        }

        $this->stream   = $filePath !== '' ? fopen($filePath, 'a') : null;
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

        if (is_resource($this->stream)) {
            fwrite($this->stream, $line . PHP_EOL);
        } else {
            error_log($line);
        }

    }
}
