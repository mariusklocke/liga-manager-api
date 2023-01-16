<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MaintenanceModeMiddleware implements MiddlewareInterface
{
    /**
     * @throws MaintenanceModeException if maintenance mode is enabled
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (file_exists(Config::getInstance()->appHome . '/.maintenance_mode')) {
            throw new MaintenanceModeException('API unavailable due to maintenance work.');
        }

        return $handler->handle($request);
    }
}
