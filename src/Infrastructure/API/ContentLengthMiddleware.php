<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class ContentLengthMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $this->logger->debug('Calculating response content length');

        $length = $response->getBody()->getSize();
        if ($length !== null) {
            $response = $response->withHeader('Content-Length', $length);
        }

        return $response;
    }
}
