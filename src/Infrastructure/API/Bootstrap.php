<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Application\Bus\ServiceProvider as CommandBusProvider;
use HexagonalPlayground\Application\Handler\ServiceProvider as CommandHandlerProvider;
use HexagonalPlayground\Infrastructure\API\GraphQL\ServiceProvider as GraphQLProvider;
use HexagonalPlayground\Infrastructure\API\Routing\RemoveTrailingSlash;
use HexagonalPlayground\Infrastructure\API\Routing\RouteProvider;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\ServiceProvider as WebAuthnServiceProvider;
use HexagonalPlayground\Infrastructure\Email\MailServiceProvider;
use HexagonalPlayground\Infrastructure\LoggerProvider;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\EventServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\Read\ReadRepositoryProvider;
use HexagonalPlayground\Infrastructure\SecurityServiceProvider;
use Pimple\ServiceProviderInterface;
use Slim\App as SlimApp;
use Slim\Container;
use Slim\Http\Request;

class Bootstrap
{
    /**
     * @return SlimApp
     */
    public static function bootstrap(): SlimApp
    {
        $app = new App(self::createContainer());
        (new RouteProvider())->registerRoutes($app);

        return $app;
    }

    /**
     * @return Container
     */
    private static function createContainer(): Container
    {
        $container = new Container([
            'settings' => [
                'determineRouteBeforeAppMiddleware' => true
            ]
        ]);
        $container['request'] = function ($container) {
            return (new RemoveTrailingSlash())->__invoke(Request::createFromEnvironment($container['environment']));
        };
        $container['errorHandler'] = function() use ($container) {
            return new ErrorHandler($container['logger']);
        };
        unset($container['phpErrorHandler']);
        unset($container['notAllowedHandler']);
        unset($container['notFoundHandler']);

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
