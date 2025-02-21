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
            $this->metrics->declare($definition->name, $this->getCollector($definition->type)->withHelp($definition->help));
        }
    }

    public function add(string $name): void
    {
        $this->metrics->add($name, 1);
    }

    public function export(): string
    {
        return file_get_contents($this->exportUrl);
    }

    public function set(string $name, float $value): void
    {
        $this->metrics->set($name, $value);
    }

    private function getCollector(string $type): Collector
    {
        switch ($type) {
            case 'counter':
                return Collector::counter();
            case 'gauge':
                return Collector::gauge();
            default:
                throw new RuntimeException("Unsupported metrics type: $type");
        }
    }
}