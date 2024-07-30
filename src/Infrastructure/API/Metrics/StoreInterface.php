<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

use Iterator;

interface StoreInterface
{
    /**
     * @param string $name
     * @return void
     */
    public function incrementCounter(string $name): void;

    /**
     * @return Metric[]
     */
    public function getMetrics(): Iterator;
}
