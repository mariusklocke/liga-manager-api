<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\API\Routing\RemoveTrailingSlash;
use HexagonalPlayground\Infrastructure\API\Routing\RouteProvider;

class Bootstrap
{
    public static function bootstrap()
    {
        $container = require __DIR__ . '/../../../config/container.php';
        $app = new App($container);
        (new RouteProvider())->registerRoutes($app);
        $app->add(new RemoveTrailingSlash());

        return $app;
    }
}