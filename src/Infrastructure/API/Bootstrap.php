<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\API\Routing\RouteProvider;
use HexagonalPlayground\Infrastructure\ContainerBuilder;
use Middlewares\TrailingSlash;
use Nyholm\Psr7\Factory\Psr17Factory;
use Slim\App;

class Bootstrap
{
    /**
     * @return App
     */
    public static function bootstrap(): App
    {
        $container = ContainerBuilder::build();

        $app = new App(new Psr17Factory(), $container);
        $app->add(new TrailingSlash());
        $app->add(new JsonParserMiddleware());
        $errorMiddleware = $app->addErrorMiddleware(false, false, false);
        $errorMiddleware->setDefaultErrorHandler(new ErrorHandler(
            $container->get('logger'),
            new Psr17Factory()
        ));

        (new RouteProvider())->registerRoutes($app, $container);

        return $app;
    }
}
