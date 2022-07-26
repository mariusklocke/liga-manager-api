<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use InvalidArgumentException;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

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

    private static ?Logger $instance = null;

    /**
     * @param resource $stream
     * @param string $minLevel
     */
    private function __construct($stream, string $minLevel)
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
     * @param resource $stream
     * @param string $minLevel
     * @return static
     */
    public static function init($stream, string $minLevel): self
    {
        self::$instance = new self($stream, $minLevel);

        return self::$instance;
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        return self::$instance;
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
        $message = $this->resolvePlaceholders($message, $context);
        $line = "[$timestamp] $level: $message";
        if (count($context)) {
            $line .= ' ' . json_encode($context);
        }
        $line .= PHP_EOL;

        fwrite($this->stream, $line);
    }

    /**
     * @param string $message
     * @param array $context
     * @return string
     */
    private function resolvePlaceholders(string $message, array $context): string
    {
        $replacements = [];

        foreach ($this->flattenArray($context) as $key => $value) {
            if (str_contains($message, '{' . $key . '}')) {
                $replacements['{' . $key . '}'] = $value;
            }
        }

        return strtr($message, $replacements);
    }

    /**
     * @param array $input
     * @return array
     */
    private function flattenArray(array $input): array
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($input));
        $result = [];

        foreach ($iterator as $leafValue) {
            $keys = [];
            foreach (range(0, $iterator->getDepth()) as $depth) {
                $keys[] = $iterator->getSubIterator($depth)->key();
            }
            $result[implode('.', $keys)] = $leafValue;
        }

        return $result;
    }
}
