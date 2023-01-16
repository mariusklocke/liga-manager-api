<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

class LoggerProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            LoggerInterface::class => DI\factory(function () {
                Logger::init(fopen('php://stdout', 'w'), Config::getInstance()->logLevel);

                return Logger::getInstance();
            })
        ];
    }
}
