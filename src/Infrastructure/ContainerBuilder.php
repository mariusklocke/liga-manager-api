<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use HexagonalPlayground\Application\ServiceProvider as ApplicationServiceProvider;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\ServiceProvider as GraphQLProvider;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\ServiceProvider as GraphQLv2Provider;
use HexagonalPlayground\Infrastructure\API\Health\ServiceProvider as HealthServiceProvider;
use HexagonalPlayground\Infrastructure\API\Security\ServiceProvider as SecurityServiceProvider;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\ServiceProvider as WebAuthnServiceProvider;
use HexagonalPlayground\Infrastructure\CLI\ServiceProvider as CliServiceProvider;
use HexagonalPlayground\Infrastructure\Email\MailServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\EventServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\Read\ReadRepositoryProvider;
use Psr\Container\ContainerInterface;

class ContainerBuilder
{
    /**
     * @return ContainerInterface
     */
    public static function build(): ContainerInterface
    {
        $builder = new \DI\ContainerBuilder();
        $builder->useAutowiring(true);

        foreach (self::getServiceProvider() as $provider) {
            $builder->addDefinitions($provider->getDefinitions());
        }

        return $builder->build();
    }

    /**
     * @return ServiceProviderInterface[]
     */
    private static function getServiceProvider(): array
    {
        return [
            new HealthServiceProvider(),
            new ApplicationServiceProvider(),
            new LoggerProvider(),
            new DoctrineServiceProvider(),
            new ReadRepositoryProvider(),
            new SecurityServiceProvider(),
            new MailServiceProvider(),
            new EventServiceProvider(),
            new GraphQLProvider(),
            new WebAuthnServiceProvider(),
            new CliServiceProvider(),
            new GraphQLv2Provider()
        ];
    }
}
