<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

interface StoreInterface
{
    /**
     * Increment counter metric
     * 
     * @param string $name
     * @param array  $labels
     */
    public function add(string $name, array $labels = []): void;

    /**
     * Exports available metrics in Prometheus format
     * 
     * @return string
     */
    public function export(): string;

    /**
     * Set gauge metric
     * 
     * @param string $name
     * @param float  $value
     * @param array  $labels
     */
    public function set(string $name, float $value, array $labels = []): void;

}
