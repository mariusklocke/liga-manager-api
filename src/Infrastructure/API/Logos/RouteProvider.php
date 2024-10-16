<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Infrastructure\API\RouteProviderInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;

class RouteProvider implements RouteProviderInterface
{
    public function register(RouteCollectorProxyInterface $routeCollectorProxy): void
    {
        $routeCollectorProxy->map(['GET', 'POST', 'DELETE'], '/logos', Controller::class);
    }
}
