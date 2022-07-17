<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

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

    public function __construct()
    {
        $this->stream = STDOUT;
        $this->minLevel = getenv('LOG_LEVEL') ?: LogLevel::NOTICE;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

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
