<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\API\Middleware\RemoveTrailingSlash;
use Slim\Container;

class Bootstrap
{
    public static function bootstrap()
    {
        $app = new App(self::buildContainer());
        (new RouteProvider())->registerRoutes($app);
        $app->add(new RemoveTrailingSlash());

        return $app;
    }

    /**
     * @return Container
     */
    public static function buildContainer()
    {
        return require __DIR__ . '/../../container.php';
    }
}