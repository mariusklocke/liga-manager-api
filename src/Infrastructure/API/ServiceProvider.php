<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            RequestFactoryInterface::class => DI\get(Psr17Factory::class),
            ResponseFactoryInterface::class => DI\get(Psr17Factory::class),
            ServerRequestFactoryInterface::class => DI\get(Psr17Factory::class),
            StreamFactoryInterface::class => DI\get(Psr17Factory::class),
            UploadedFileFactoryInterface::class => DI\get(Psr17Factory::class),
            UriFactoryInterface::class => DI\get(Psr17Factory::class),
            LoggerInterface::class => DI\factory(function (ContainerInterface $container) {
                /** @var FilesystemService $filesystem */
                $filesystem = $container->get(FilesystemService::class);

                return new Logger(
                    $filesystem->openFile($container->get('config.api.logPath'), 'w'),
                    $container->get('config.api.logLevel')
                );
            }),
            MaintenanceModeMiddleware::class => DI\factory(function (ContainerInterface $container) {
                return new MaintenanceModeMiddleware($container->get('config.api.maintenanceMode') === 'on');
            }),
            'config.api.jwtSecret' => DI\env('JWT_SECRET', ''),
            'config.api.logLevel' => DI\env('LOG_LEVEL', 'debug'),
            'config.api.logPath' => DI\env('LOG_PATH', 'php://stderr'),
            'config.api.maintenanceMode' => DI\env('MAINTENANCE_MODE', 'off'),
            'config.api.rateLimit' => DI\env('RATE_LIMIT', ''),
        ];
    }
}
