<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Application\Bus\ServiceProvider as CommandBusProvider;
use HexagonalPlayground\Application\Handler\ServiceProvider as CommandHandlerProvider;
use HexagonalPlayground\Infrastructure\API\GraphQL\ServiceProvider as GraphQLProvider;
use HexagonalPlayground\Infrastructure\API\Routing\RouteProvider;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\ServiceProvider as WebAuthnServiceProvider;
use HexagonalPlayground\Infrastructure\Email\MailServiceProvider;
use HexagonalPlayground\Infrastructure\LoggerProvider;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\EventServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\Read\ReadRepositoryProvider;
use HexagonalPlayground\Infrastructure\SecurityServiceProvider;
use Middlewares\TrailingSlash;
use Nyholm\Psr7\Factory\Psr17Factory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Pimple\Psr11\Container as PsrContainerWrapper;
use Slim\App;

class Bootstrap
{
    /**
     * @return App
     */
    public static function bootstrap(): App
    {
        $container = new PsrContainerWrapper(self::createContainer());
        $app = new App(new Psr17Factory(), $container);
        $app->add(new TrailingSlash());
        $app->add(new JsonParserMiddleware());

        (new RouteProvider())->registerRoutes($app, $container);

        return $app;
    }

    /**
     * @return Container
     */
    private static function createContainer(): Container
    {
        $container = new Container();

        foreach (self::getServiceProvider() as $provider) {
            $provider->register($container);
        }

        return $container;
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
            new WebAuthnServiceProvider()
        ];
    }
}
