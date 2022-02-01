<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Health;

use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use RuntimeException;

class FpmHealthCheck implements HealthCheckInterface
{
    public function __invoke(): void
    {
        $connection = @fsockopen('127.0.0.1', 9000);

        if (!is_resource($connection)) {
            throw new RuntimeException('FPM socket not available');
        }

        fclose($connection);
    }

    public function getName(): string
    {
        return 'fpm';
    }
}
