<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\API\Routing\RouteProvider;
use HexagonalPlayground\Infrastructure\API\Security\JsonSchemaValidator;

class Bootstrap
{
    public static function bootstrap()
    {
        $container = require __DIR__ . '/../../../config/container.php';
        $container['settings']['determineRouteBeforeAppMiddleware'] = true;
        $app = new App($container);
        (new RouteProvider())->registerRoutes($app);
        //$app->add(new JsonSchemaValidator(__DIR__  . '/../../../public/swagger.json'));

        return $app;
    }
}