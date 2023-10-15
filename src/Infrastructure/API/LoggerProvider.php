<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\Config;
use Psr\Log\LoggerInterface;

class LoggerProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            LoggerInterface::class => DI\factory(function () {
                $config = Config::getInstance();
                $stream = fopen($config->logPath, 'w');
                $logLevel = $config->logLevel;

                return new Logger($stream, $logLevel);
            })
        ];
    }
}
