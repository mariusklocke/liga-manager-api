<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Health;

use HexagonalPlayground\Infrastructure\API\RouteProviderInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;

class RouteProvider implements RouteProviderInterface
{
    public function register(RouteCollectorProxyInterface $routeCollectorProxy): void
    {
        $routeCollectorProxy->get('/health', Controller::class . ':health');
    }
}