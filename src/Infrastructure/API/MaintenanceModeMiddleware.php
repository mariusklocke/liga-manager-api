<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MaintenanceModeMiddleware implements MiddlewareInterface
{
    private bool $enabled;

    /**
     * @param bool $enabled
     */
    public function __construct(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @throws MaintenanceModeException if maintenance mode is enabled
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->enabled) {
            throw new MaintenanceModeException('API unavailable due to maintenance work.');
        }

        return $handler->handle($request);
    }
}
