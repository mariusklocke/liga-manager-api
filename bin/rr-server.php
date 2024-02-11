<?php
require_once __DIR__ . '/../vendor/autoload.php';

use HexagonalPlayground\Infrastructure\API\Application;
use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\Http\PSR7Worker;

$app = new Application();
$worker = Worker::create();
$psrFactory = new Psr17Factory();
$psrWorker = new PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

while (true) {
    try {
        $request = $psrWorker->waitRequest();
        if ($request === null) {
            break;
        }
    } catch (\Throwable $e) {
        $psrWorker->respond($psrFactory->createResponse(400));
        continue;
    }

    try {
        $psrWorker->respond($app->handle($request));
    } catch (\Throwable $e) {
        $psrWorker->respond($psrFactory->createResponse(500));
        $worker->error((string)$e);
    }
}
