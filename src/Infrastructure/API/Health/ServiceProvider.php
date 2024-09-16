<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Health;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\API\ResponseSerializer;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            QueryAction::class => DI\create()->constructor(
                DI\get(HealthCheckInterface::class),
                DI\get(ResponseSerializer::class),
                DI\get('app.version')
            )
        ];
    }
}
