<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Health;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\API\JsonResponseWriter;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            HealthCheckInterface::class => [],
            QueryAction::class => DI\create()->constructor(DI\get(HealthCheckInterface::class), DI\get(JsonResponseWriter::class))
        ];
    }
}