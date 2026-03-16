<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

use APCUIterator;
use Iterator;

class ApcuStore implements StoreInterface
{
    private array $definitions;

    /**
     * @param Definition[] $definitions
     */
    public function __construct(array $definitions)
    {
        $this->definitions = $definitions;
    }

    public function add(string $name, array $labels = []): void
    {
        \apcu_inc($this->buildKey($name, $labels));
    }

    public function export(): string
    {
        $result = [];

        foreach ($this->definitions as $definition) {
            $result[] = sprintf('# HELP %s %s', $definition->name, $definition->help);
            $result[] = sprintf('# TYPE %s %s', $definition->name, $definition->type);
            $valuePattern = $definition->type === 'counter' ? '%s %d' : '%s %e';
            foreach ($this->getValues($definition->name) as $key => $value) {
                if (str_ends_with($key, '{}')) {
                    $key = str_replace('{}', '', $key);
                }
                $result[] = sprintf($valuePattern, $key, $value);
            }
        }

        return implode(PHP_EOL, $result);
    }

    public function set(string $name, float $value, array $labels = []): void
    {
        \apcu_store($this->buildKey($name, $labels), $value);
    }

    private function getValues(string $name): Iterator
    {
        foreach (new APCUIterator() as $key => $value) {
            if (str_starts_with($key, "metrics.$name{") && str_ends_with($key, '}')) {
                yield str_replace('metrics.', '', $key) => $value;
            }
        }
    }

    private function buildKey(string $name, array $labels = []): string
    {
        return 'metrics.' . $name . $this->formatLabels($labels);
    }

    private function formatLabels(array $labels): string
    {
        ksort($labels);
        $result = [];
        foreach ($labels as $key => $value) {
            $result[] = sprintf('%s="%s"', $key, $value);
        }
        return '{' . implode(',', $result) . '}';
    }
}
