<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Reporter;

use DI;
use GuzzleHttp\Client;
use HexagonalPlayground\Application\ErrorReporter;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\Config;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            SentryReporter::class => DI\factory(
                fn(ContainerInterface $container) => new SentryReporter(
                    $container->get(Client::class),
                    $container->get(RequestFactoryInterface::class),
                    $container->get(StreamFactoryInterface::class),
                    $container->get(UriFactoryInterface::class)->createUri(
                        $container->get(Config::class)->getValue('sentry.url', 'null://localhost')
                    ),
                ),
            ),
            ErrorReporter::class => DI\get(SentryReporter::class)
        ];
    }
}
