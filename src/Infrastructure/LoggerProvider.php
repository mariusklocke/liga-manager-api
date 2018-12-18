<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class LoggerProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     */
    public function register(Container $container)
    {
        $container['logger'] = function() {
            $level    = Logger::toMonologLevel(Environment::get('LOG_LEVEL'));
            $filename = Environment::get('LOG_PATH') . '/app.log';
            $handler  = new RotatingFileHandler($filename, 0, $level);
            $handler->setFormatter(new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message% %context%\n"
            ));
            return new Logger('logger', [$handler]);
        };
    }
}