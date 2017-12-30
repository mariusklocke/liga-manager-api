<?php

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/container.php';
$app = new \Slim\App($container);
(new \HexagonalDream\Infrastructure\API\RouteProvider())->registerRoutes($app);

return $app;