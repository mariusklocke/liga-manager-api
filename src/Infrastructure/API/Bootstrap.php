<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\API\Routing\RouteProvider;
use HexagonalPlayground\Infrastructure\API\Security\JsonSchemaValidator;
use Slim\App;

class Bootstrap extends \HexagonalPlayground\Application\Bootstrap
{
    public static function bootstrap(): App
    {
        $container = require __DIR__ . '/../../../config/container.php';
        $container['settings']['determineRouteBeforeAppMiddleware'] = true;
        $app = new App($container);
        (new RouteProvider())->registerRoutes($app);
        //$app->add(new JsonSchemaValidator(__DIR__  . '/../../../public/swagger.json'));
        parent::configureEventPublisher($container);

        return $app;
    }
}