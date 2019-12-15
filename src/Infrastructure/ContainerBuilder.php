<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use HexagonalPlayground\Application\Bus\ServiceProvider as CommandBusProvider;
use HexagonalPlayground\Application\Handler\ServiceProvider as CommandHandlerProvider;
use HexagonalPlayground\Application\Import\L98ImportProvider;
use HexagonalPlayground\Infrastructure\API\GraphQL\ServiceProvider as GraphQLProvider;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\ServiceProvider as WebAuthnServiceProvider;
use HexagonalPlayground\Infrastructure\CLI\ServiceProvider as CliServiceProvider;
use HexagonalPlayground\Infrastructure\Email\MailServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\EventServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\Read\ReadRepositoryProvider;
use Pimple\Psr11\Container as PsrContainerWrapper;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class ContainerBuilder
{
    /**
     * @return ContainerInterface
     */
    public static function build(): ContainerInterface
    {
        $container = new Container();

        foreach (self::getServiceProvider() as $provider) {
            $provider->register($container);
        }

        return new PsrContainerWrapper($container);
    }

    /**
     * @return ServiceProviderInterface[]
     */
    private static function getServiceProvider(): array
    {
        return [
            new CommandBusProvider(),
            new CommandHandlerProvider(),
            new LoggerProvider(),
            new DoctrineServiceProvider(),
            new ReadRepositoryProvider(),
            new SecurityServiceProvider(),
            new MailServiceProvider(),
            new EventServiceProvider(),
            new GraphQLProvider(),
            new WebAuthnServiceProvider(),
            new CliServiceProvider(),
            new L98ImportProvider()
        ];
    }
}