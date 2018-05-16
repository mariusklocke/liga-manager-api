<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use Monolog\Handler\StreamHandler;
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
            $stream = 'php://stdout';
            if (php_sapi_name() !== 'cli') {
                $stream = getenv('LOG_STREAM') ?: $stream;
            }
            $level = Logger::toMonologLevel(getenv('LOG_LEVEL') ?: 'warning');
            $handler = new StreamHandler(fopen($stream, 'a'), $level);
            return new Logger('logger', [$handler]);
        };
    }
}