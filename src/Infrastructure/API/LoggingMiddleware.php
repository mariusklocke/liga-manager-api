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

class LoggingMiddleware implements MiddlewareInterface
{
    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /** @var Timer */
    private Timer $timer;

    private static array $loggableHeaders = [
        'Content-Length',
        'Content-Type',
        'Referer',
        'User-Agent'
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerInterface::class);
        $this->timer = new Timer();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestId = bin2hex(random_bytes(4));
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        $headers = [];
        foreach (self::$loggableHeaders as $headerName) {
            $headers[$headerName] = $request->getHeader($headerName);
        }

        $this->logger->debug("Received request $requestId $method $path", ['headers' => $headers]);
        $this->timer->start();
        $response = $handler->handle($request);
        $processingTime = $this->timer->stop();
        $this->logger->debug("Sending response for request $requestId after $processingTime ms");

        return $response;
    }
}
