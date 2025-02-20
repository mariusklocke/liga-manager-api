<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Metrics\Collector;
use Spiral\RoadRunner\Metrics\Metrics;

class RoadRunnerStore implements StoreInterface
{
    private Metrics $metrics;

    public function __construct(array $counters, array $gauges)
    {
        $this->metrics = new Metrics(RPC::create('tcp://127.0.0.1:6001'));
        
        foreach ($counters as $name => $help) {
            $this->metrics->declare($name, Collector::counter()->withHelp($help));
        }

        foreach ($gauges as $name => $help) {
            $this->metrics->declare($name, Collector::gauge()->withHelp($help));
        }
    }

    public function add(string $name): void
    {
        $this->metrics->add($name, 1);
    }

    public function export(): string
    {
        return file_get_contents('http://127.0.0.1:8081');
    }

    public function set(string $name, float $value): void
    {
        $this->metrics->set($name, $value);
    }
}