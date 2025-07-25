<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\Config;
use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Stringable;
use Throwable;

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
            try {
                $encodedContext = $this->encodeContext($context);
            } catch (Throwable) {
                $encodedContext = '{}';
            }
            $line .= ' ' . $encodedContext;
        }

        if (is_resource($this->stream)) {
            fwrite($this->stream, $line . PHP_EOL);
        } else {
            error_log($line);
        }
    }

    private function encodeContext(array $context): string
    {
        if (isset($context['request']) && $context['request'] instanceof MessageInterface) {
            $context['request'] = $this->serializeMessage($context['request']);
        }

        if (isset($context['response']) && $context['response'] instanceof MessageInterface) {
            $context['response'] = $this->serializeMessage($context['response']);
        }

        return json_encode($context);
    }

    private function serializeMessage(MessageInterface $message): array
    {
        return [
            'protocol' => sprintf("HTTP/%s", $message->getProtocolVersion()),
            'headers' => $this->anonymizeHeaders($message),
            'body' => (string)$message->getBody()
        ];
    }

    private function anonymizeHeaders(MessageInterface $message): array
    {
        $headers = $message->getHeaders();

        if (isset($headers['Cookie'])) {
            $headers['Cookie'] = array_fill(0, count($headers['Cookie']), '--redacted---');
        }

        if (isset($headers['Authorization'])) {
            $headers['Authorization'] = array_map(function (string $value): string {
                return preg_replace('/(\S+) (\S+)/', '$1 ---redacted---', $value);
            }, $headers['Authorization']);
        }

        if (isset($headers['X-Token'])) {
            $headers['X-Token'] = array_fill(0, count($headers['X-Token']), '--redacted---');
        }

        return $headers;
    }
}
