<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
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
            Logger::class => DI\autowire(),
            LoggerInterface::class => DI\get(Logger::class),
            MaintenanceModeMiddleware::class => DI\factory(function (ContainerInterface $container) {
                return new MaintenanceModeMiddleware(
                    $container->get('app.home'),
                    $container->get(LoggerInterface::class)
                );
            })
        ];
    }
}
