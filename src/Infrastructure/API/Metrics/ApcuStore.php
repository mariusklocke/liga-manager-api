<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

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

    public function add(string $name): void
    {
        \apcu_inc($this->buildKey($name));
    }

    public function export(): string
    {
        $result = [];

        foreach ($this->definitions as $definition) {
            $result[] = sprintf('# HELP %s %s', $definition->name, $definition->help);
            $result[] = sprintf('# TYPE %s %s', $definition->name, $definition->type);
            if ($definition->type === 'counter') {
                $result[] = sprintf('%s %d', $definition->name, $this->get($definition->name));
            } else {
                $result[] = sprintf('%s %e', $definition->name, $this->get($definition->name));
            }
        }

        return implode(PHP_EOL, $result);
    }

    public function set(string $name, float $value): void
    {
        \apcu_store($this->buildKey($name), $value);
    }

    private function get(string $name): mixed
    {
        return \apcu_fetch($this->buildKey($name));
    }

    private function buildKey(string $name): string
    {
        return 'metrics.' . $name;
    }
}
