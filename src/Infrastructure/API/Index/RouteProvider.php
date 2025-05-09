<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Index;

use HexagonalPlayground\Infrastructure\API\RouteProviderInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;

class RouteProvider implements RouteProviderInterface
{
    public function register(RouteCollectorProxyInterface $routeCollectorProxy): void
    {
        $routeCollectorProxy->get('/', Controller::class);
    }
}
