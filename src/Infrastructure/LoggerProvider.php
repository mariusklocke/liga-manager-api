<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class LoggerProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['logger'] = function() {
            $level    = Logger::toMonologLevel(Environment::get('LOG_LEVEL'));
            $handler  = new StreamHandler(fopen('php://stdout', 'w'), $level);
            $handler->setFormatter(new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message% %context%\n"
            ));
            return new Logger('logger', [$handler]);
        };
    }
}