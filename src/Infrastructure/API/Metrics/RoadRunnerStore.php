<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

use RuntimeException;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Metrics\Collector;
use Spiral\RoadRunner\Metrics\Metrics;

class RoadRunnerStore implements StoreInterface
{
    private Metrics $metrics;
    private string $exportUrl;

    /**
     * @param Definition[] $definitions
     * @param string $exportUrl
     * @param string $publishUrl
     */
    public function __construct(array $definitions, string $exportUrl, string $publishUrl)
    {
        $this->metrics = new Metrics(RPC::create($publishUrl));
        $this->exportUrl = $exportUrl;

        foreach ($definitions as $definition) {
            $this->metrics->declare($definition->name, $this->getCollector($definition));
        }
    }

    public function add(string $name, array $labels = []): void
    {
        ksort($labels);
        $processedLabels = [];
        foreach ($labels as $value) {
            $processedLabels[] = (string)$value;
        }
        $this->metrics->add($name, 1, $processedLabels);
    }

    public function export(): string
    {
        return file_get_contents($this->exportUrl);
    }

    public function set(string $name, float $value, array $labels = []): void
    {
        ksort($labels);
        $processedLabels = [];
        foreach ($labels as $value) {
            $processedLabels[] = (string)$value;
        }
        $this->metrics->set($name, $value, $processedLabels);
    }

    private function getCollector(Definition $definition): Collector
    {
        switch ($definition->type) {
            case 'counter':
                return Collector::counter()->withHelp($definition->help)->withLabels(...$definition->labels);
            case 'gauge':
                return Collector::gauge()->withHelp($definition->help)->withLabels(...$definition->labels);
            default:
                throw new RuntimeException("Unsupported metrics type: " . $definition->type);
        }
    }
}