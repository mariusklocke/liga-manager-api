<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\Timer;
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

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->timer = new Timer();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestId = bin2hex(random_bytes(4));
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $serverParams = $request->getServerParams();
        $remoteAddress = $serverParams['REMOTE_ADDR'] ?? null;

        $this->logger->debug("Received request \"$method $path\"", [
            'remoteAddress' => $remoteAddress,
            'requestId' => $requestId,
            'request' => $request
        ]);

        $this->timer->start();
        $response = $handler->handle($request);
        $processingTime = $this->timer->stop();

        $this->logger->debug("Sending response for \"$method $path\"", [
            'statusCode' => $response->getStatusCode(),
            'remoteAddress' => $remoteAddress,
            'processingTime' => sprintf('%d ms', $processingTime),
            'requestId' => $requestId,
            'response' => $response
        ]);

        return $response;
    }
}
