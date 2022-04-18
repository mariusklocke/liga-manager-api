<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\API\GraphQL\RouteProvider as GraphQLRouteProvider;
use HexagonalPlayground\Infrastructure\API\Health\RouteProvider as HealthRouteProvider;
use HexagonalPlayground\Infrastructure\API\Security\AuthenticationMiddleware;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\RouteProvider as WebAuthnRouteProvider;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\RouteProvider as GraphQlv2RouteProvider;
use HexagonalPlayground\Infrastructure\ContainerBuilder;
use Middlewares\TrailingSlash;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;

class Bootstrap
{
    /**
     * @return App
     */
    public static function bootstrap(): App
    {
        $container = ContainerBuilder::build();

        $app = new App(new Psr17Factory(), $container);
        $app->add(new ProfilingMiddleware($container));
        $app->add(new TrailingSlash());
        $app->add(new JsonParserMiddleware());
        $app->add(new AuthenticationMiddleware($container));
        $app->add(new MaintenanceModeMiddleware());

        $errorMiddleware = $app->addErrorMiddleware(false, false, false);
        $errorMiddleware->setDefaultErrorHandler(new ErrorHandler(
            $container->get(LoggerInterface::class),
            new Psr17Factory(),
            $container->get(JsonResponseWriter::class)
        ));

        $app->group('/api', function (RouteCollectorProxyInterface $group) {
            foreach (self::getRouteProvider() as $provider) {
                $provider->register($group);
            }
        });

        return $app;
    }

    /**
     * @return RouteProviderInterface[]
     */
    private static function getRouteProvider(): array
    {
        return [
            new GraphQLRouteProvider(),
            new GraphQlv2RouteProvider(),
            new WebAuthnRouteProvider(),
            new HealthRouteProvider()
        ];
    }
}
