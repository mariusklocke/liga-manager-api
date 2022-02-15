<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\Timer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class ProfilingMiddleware implements MiddlewareInterface
{
    /** @var ContainerInterface */
    private ContainerInterface $container;

    /** @var Timer */
    private Timer $timer;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->timer = new Timer();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $logger = $this->container->get(LoggerInterface::class);
        $this->timer->start();
        $response = $handler->handle($request);
        $logger->debug('Response time: {responseTime} ms', [
            'responseTime' => $this->timer->stop()
        ]);

        return $response;
    }
}
