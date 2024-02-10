<?php
require_once __DIR__ . '/../vendor/autoload.php';

use HexagonalPlayground\Infrastructure\API\Application;
use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\Http\PSR7Worker;

$app = new Application();
$worker = Worker::create();
$factory = new Psr17Factory();
$psr7 = new PSR7Worker($worker, $factory, $factory, $factory);

while (true) {
    try {
        $request = $psr7->waitRequest();
        if ($request === null) {
            break;
        }
    } catch (\Throwable $e) {
        $psr7->respond($factory->createResponse(400));
        continue;
    }

    try {
        $psr7->respond($app->handle($request));
    } catch (\Throwable $e) {
        $psr7->respond($factory->createResponse(500));
        $worker->error((string)$e);
    }
}
