<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

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

    public function add(string $name): void
    {
        \apcu_inc($this->buildKey($name));
    }

    public function export(): string
    {
        $result = [];

        foreach ($this->counters as $name => $help) {
            $value = (int)\apcu_fetch($this->buildKey($name));
            $result[] = sprintf('# HELP %s %s', $name, $help);
            $result[] = sprintf('# TYPE %s %s', $name, 'counter');
            $result[] = sprintf('%s %d', $name, $value);
        }

        foreach ($this->gauges as $name => $help) {
            $value = (float)\apcu_fetch($this->buildKey($name));
            $result[] = sprintf('# HELP %s %s', $name, $help);
            $result[] = sprintf('# TYPE %s %s', $name, 'gauge');
            $result[] = sprintf('%s %e', $name, $value);
        }

        return implode(PHP_EOL, $result);
    }

    public function set(string $name, float $value): void
    {
        \apcu_store($this->buildKey($name), $value);
    }

    private function buildKey(string $name): string
    {
        return self::KEY_PREFIX . '.' . $name;
    }
}
