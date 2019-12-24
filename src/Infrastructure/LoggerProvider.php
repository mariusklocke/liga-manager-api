<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LoggerProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            LoggerInterface::class => DI\get(Logger::class),

            Logger::class => DI\create()
                ->constructor('logger')
                ->method('pushHandler', DI\get(StreamHandler::class)),

            StreamHandler::class => DI\create()
                ->constructor('php://stdout', DI\env('LOG_LEVEL'))
                ->method('setFormatter', DI\get(LineFormatter::class)),

            LineFormatter::class => DI\create()
                ->constructor("[%datetime%] %channel%.%level_name%: %message% %context%\n")
        ];
    }
}