<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Index;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Controller::class => DI\factory(function (ContainerInterface $container) {
                return new Controller(
                    $container->get(ResponseFactoryInterface::class),
                    $container->get('app.version')
                );
            }),
        ];
    }
}
