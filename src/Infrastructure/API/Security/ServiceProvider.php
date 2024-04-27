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
            JsonWebTokenService::class => DI\create()->constructor(
                DI\get('config.api.jwtSecret')
            ),
            RateLimitMiddleware::class => DI\create()->constructor(
                DI\get('config.api.rateLimit')
            ),
            PasswordAuthenticator::class => DI\autowire(),
            TokenAuthenticator::class => DI\autowire(),
            AccessLinkGenerator::class => DI\create()->constructor(
                DI\get(TokenServiceInterface::class),
                DI\get('config.api.appBaseUrl')
            ),
            AccessLinkGeneratorInterface::class => DI\get(AccessLinkGenerator::class)
        ];
    }
}
