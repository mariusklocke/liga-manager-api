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
        $clientIp = $this->extractClientIp($request);

        $this->logger->debug("Received request:", [
            'method' => $method,
            'path' => $path,
            'clientIp' => $clientIp,
            'requestId' => $requestId,
            'protocol' => sprintf("HTTP/%s", $request->getProtocolVersion()),
            'headers' => $this->extractHeaders($request)
        ]);

        $this->timer->start();
        $response = $handler->handle($request);
        $processingTime = $this->timer->stop();

        $this->logger->debug("Sending response:", [
            'method' => $method,
            'path' => $path,
            'clientIp' => $clientIp,
            'requestId' => $requestId,
            'status' => $response->getStatusCode(),
            'size' => $response->getBody()->getSize(),
            'timeMs' => $processingTime
        ]);

        return $response;
    }

    private function extractHeaders(ServerRequestInterface $request): array
    {
        $result = [];

        foreach ($request->getHeaders() as $name => $values) {
            if (count($values) === 0 || $values[0] === '') {
                continue;
            }
            $value = $values[0];

            switch ($name) {
                case 'Authorization':
                    $segments = explode(' ', $value, 2);
                    $result[$name] = $segments[0];
                    break;
                case 'Content-Length':
                case 'Content-Type':
                case 'Referer':
                case 'User-Agent':
                    $result[$name] = $value;
                    break;
            }
        }

        return $result;
    }

    private function extractClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        return $serverParams['REMOTE_ADDR'] ?? '';
    }
}
