<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DI;
use HexagonalPlayground\Application\Security\TokenServiceInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            TokenServiceInterface::class => DI\get(JsonWebTokenService::class),
            PasswordAuthenticator::class => DI\autowire(),
            TokenAuthenticator::class => DI\autowire()
        ];
    }
}
