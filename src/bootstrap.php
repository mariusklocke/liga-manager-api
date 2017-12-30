<?php

use Slim\App;

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/container.php';
$app = new App($container);
(new \HexagonalDream\Infrastructure\API\ControllerProvider())->registerRoutes($app);

return $app;