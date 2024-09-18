<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Health;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Controller::class => DI\create()->constructor(
                DI\get(ResponseFactoryInterface::class),
                DI\get(HealthCheckInterface::class),
                DI\get('app.version')
            ),
        ];
    }
}
