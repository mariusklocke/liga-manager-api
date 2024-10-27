<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DI;
use HexagonalPlayground\Application\Security\AccessLinkGeneratorInterface;
use HexagonalPlayground\Application\Security\TokenServiceInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            TokenServiceInterface::class => DI\get(JsonWebTokenService::class),
            JsonWebTokenService::class => DI\autowire(),
            RateLimitMiddleware::class => DI\autowire(),
            PasswordAuthenticator::class => DI\autowire(),
            TokenAuthenticator::class => DI\autowire(),
            AccessLinkGenerator::class => DI\autowire(),
            AccessLinkGeneratorInterface::class => DI\get(AccessLinkGenerator::class)
        ];
    }
}
