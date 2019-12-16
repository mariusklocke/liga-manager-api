<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class ServiceProvider implements ServiceProviderInterface
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
        $container[HandlerResolver::class] = function () use ($container) {
            return new ContainerHandlerResolver(new \Pimple\Psr11\Container($container));
        };
        $container['commandBus'] = function() use ($container) {
            return new CommandBus($container[HandlerResolver::class], $container[OrmTransactionWrapperInterface::class]);
        };
        $container[BatchCommandBus::class] = function () use ($container) {
            return new BatchCommandBus($container[HandlerResolver::class], $container[OrmTransactionWrapperInterface::class]);
        };
    }
}
