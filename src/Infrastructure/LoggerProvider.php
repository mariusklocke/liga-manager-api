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
            $stream = null;
            if ($path = getenv('LOG_PATH')) {
                if (strpos($path, '/') !== 0) {
                    // Make path relative to application root
                    $path = __DIR__ . '/../' . $path;
                }
                $stream = fopen($path, 'a');
            }
            if (!is_resource($stream)) {
                $path = 'php://stdout';
                if (php_sapi_name() !== 'cli') {
                    $path = getenv('LOG_STREAM') ?: $path;
                }
                $stream = fopen($path, 'a');
            }
            $level = Logger::toMonologLevel(getenv('LOG_LEVEL') ?: 'warning');
            $handler = new StreamHandler($stream, $level);
            return new Logger('logger', [$handler]);
        };
    }
}