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
        $protocol = sprintf("HTTP/%s", $request->getProtocolVersion());
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $serverParams = $request->getServerParams();

        $this->logger->debug("Received \"$method $path\"", [
            'protocol' => $protocol,
            'remoteAddress' => $serverParams['REMOTE_ADDR'],
            'requestId' => $requestId,
            'headers' => $this->extractHeaders($request)
        ]);

        $this->timer->start();
        $response = $handler->handle($request);
        $processingTime = $this->timer->stop();
        $status = $response->getStatusCode();

        $this->logger->debug("Handled \"$method $path\" with status $status", [
            'requestId' => $requestId,
            'bodySize' => sprintf('%d bytes', $response->getBody()->getSize()),
            'processingTime' => sprintf('%d ms', $processingTime),
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

            if ($name === 'Cookie') {
                continue;
            }

            if ($name === 'Authorization') {
                $segments = explode(' ', $value, 2);
                $result[$name] = $segments[0];
                continue;
            }

            $result[$name] = $value;
        }

        return $result;
    }
}
