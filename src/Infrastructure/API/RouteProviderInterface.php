<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Slim\Interfaces\RouteCollectorProxyInterface;

interface RouteProviderInterface
{
    public function register(RouteCollectorProxyInterface $routeCollectorProxy): void;
}