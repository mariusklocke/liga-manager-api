<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use HexagonalPlayground\Infrastructure\API\Security\Authenticator;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Infrastructure\API\Security\JsonWebTokenFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SecurityServiceProvider implements ServiceProviderInterface
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
        $container[TokenFactoryInterface::class] = function () {
            return new JsonWebTokenFactory();
        };
        $container[Authenticator::class] = function () use ($container) {
            return new Authenticator($container[TokenFactoryInterface::class], $container['orm.repository.user']);
        };
    }
}