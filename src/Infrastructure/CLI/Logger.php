<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Infrastructure\Config;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;
use Stringable;

class Logger extends AbstractLogger
{
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

    public function __construct(
        private OutputInterface $output,
        private Config $config,
    ) {}

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $minLevel = $this->config->getValue('log.level', LogLevel::DEBUG);
        if (self::$severityMap[$level] < self::$severityMap[$minLevel]) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s P');
        $level = strtoupper($level);
        $line = "[$timestamp] $level: $message";
        if (count($context)) {
            $line .= ' ' . json_encode($context);
        }

        $this->output->writeln($line);
    }
}