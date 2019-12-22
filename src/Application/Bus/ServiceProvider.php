<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            HandlerResolver::class => DI\get(ContainerHandlerResolver::class),
            ContainerHandlerResolver::class => DI\autowire(),
            CommandBus::class => DI\autowire(),
            BatchCommandBus::class => DI\autowire()
        ];
    }
}
