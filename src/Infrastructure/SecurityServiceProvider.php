<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\API\Security\Authenticator;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Infrastructure\API\Security\JsonWebTokenFactory;

class SecurityServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            TokenFactoryInterface::class => DI\get(JsonWebTokenFactory::class),
            JsonWebTokenFactory::class => DI\autowire(),
            Authenticator::class => DI\autowire()
        ];
    }
}