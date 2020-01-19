<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use Exception;

interface HealthCheckInterface
{
    /**
     * @throws Exception
     */
    public function __invoke(): void;

    /**
     * @return string
     */
    public function getDescription(): string;
}