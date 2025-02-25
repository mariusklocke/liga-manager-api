<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker as RoadRunnerWorker;

class Worker extends PSR7Worker
{
    private Application $app;

    public function __construct()
    {
        $this->app = new Application();

        $logger = $this->app->getContainer()->get(LoggerInterface::class);
        $worker = RoadRunnerWorker::create(true, $logger);
        $requestFactory = $this->app->getContainer()->get(ServerRequestFactoryInterface::class);
        $streamFactory = $this->app->getContainer()->get(StreamFactoryInterface::class);
        $uploadsFactory = $this->app->getContainer()->get(UploadedFileFactoryInterface::class);
        
        parent::__construct($worker, $requestFactory, $streamFactory, $uploadsFactory);
    }

    public function run(): void
    {
        $logger = $this->app->getContainer()->get(LoggerInterface::class);
        $logger->info('Roadrunner worker started: Waiting for requests', [
            'versions' => [
                'app' => getenv('APP_VERSION'),
                'php' => getenv('PHP_VERSION'),
            ]
        ]);

        while (true) {
            try {
                $request = $this->waitRequest();
                if ($request === null) {
                    break;
                }
            } catch (\Throwable $e) {
                $this->respond($this->app->getResponseFactory()->createResponse(400));
                continue;
            }

            try {
                $response = $this->app->handle($request);
            } catch (\Throwable $e) {
                $response = $this->app->getResponseFactory()->createResponse(500);
                $logger->error((string)$e);
            } finally {
                $this->respond($response);
                if ($response->getStatusCode() === 500) {
                    // in case of unexpected error the application state can be corrupted: it's safer to terminate
                    $logger->error('Terminating worker process due to unexpected error.');
                    exit(1);
                }
            }
        }
    }
}