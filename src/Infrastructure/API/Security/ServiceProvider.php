<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            TokenFactoryInterface::class => DI\get(JsonWebTokenFactory::class),
            JsonWebTokenFactory::class => DI\autowire(),
            PasswordAuthenticator::class => DI\autowire(),
            TokenAuthenticator::class => DI\autowire()
        ];
    }
}