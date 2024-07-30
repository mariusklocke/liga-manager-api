<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

use InvalidArgumentException;
use Iterator;

class ApcuStore implements StoreInterface
{
    private const KEY_PREFIX = 'metrics';
    private array $counters;
    private array $gauges;

    public function __construct(array $counters, array $gauges)
    {
        $this->counters = $counters;
        $this->gauges = $gauges;
    }

    public function getCounterValue(string $name): int
    {
        $this->assertCounterExists($name);

        $value = \apcu_fetch($this->buildKey($name));

        return (int)$value;
    }

    public function getGaugeValue(string $name): int
    {
        switch ($name) {
            case 'memory_usage':
                return \memory_get_usage();
            case 'memory_peak_usage':
                return \memory_get_peak_usage();
        }

        throw new InvalidArgumentException("Unknown gauge metric: $name");
    }

    public function getMetrics(): Iterator
    {
        foreach ($this->counters as $name => $help) {
            yield new Metric($name, 'counter', $help, $this->getCounterValue($name));
        }
        foreach ($this->gauges as $name => $help) {
            yield new Metric($name, 'gauge', $help, $this->getGaugeValue($name));
        }
    }

    public function incrementCounter(string $name): void
    {
        $this->assertCounterExists($name);
        \apcu_inc($this->buildKey($name));
    }

    private function buildKey(string $name): string
    {
        return self::KEY_PREFIX . '.' . $name;
    }

    private function assertCounterExists(string $name): void
    {
        if (!array_key_exists($name, $this->counters)) {
            throw new InvalidArgumentException("Unknown counter metric: $name");
        }
    }
}
