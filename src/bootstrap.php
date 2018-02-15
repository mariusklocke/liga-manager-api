<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/container.php';
$app = new \HexagonalPlayground\Infrastructure\API\App($container);
(new \HexagonalPlayground\Infrastructure\API\RouteProvider())->registerRoutes($app);
$app->add(new \HexagonalPlayground\Infrastructure\API\Middleware\RemoveTrailingSlash());

return $app;