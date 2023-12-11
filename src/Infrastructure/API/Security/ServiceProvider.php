<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DI;
use HexagonalPlayground\Application\Security\TokenServiceInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\Config;
use Psr\Container\ContainerInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            TokenServiceInterface::class => DI\get(JsonWebTokenService::class),
            JsonWebTokenService::class => DI\factory(function (ContainerInterface $container) {
                /** @var Config $config */
                $config = $container->get(Config::class);

                return new JsonWebTokenService($config->jwtSecret);
            }),
            PasswordAuthenticator::class => DI\autowire(),
            TokenAuthenticator::class => DI\autowire()
        ];
    }
}
