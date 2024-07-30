<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\ResponseSerializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class QueryAction implements ActionInterface
{
    private ResponseSerializer $serializer;
    private StoreInterface $metricsStore;

    public function __construct(ResponseSerializer $serializer, StoreInterface $metricsStore)
    {
        $this->serializer = $serializer;
        $this->metricsStore = $metricsStore;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $result = '';

        foreach ($this->metricsStore->getMetrics() as $metric) {
            $result .= sprintf('# HELP %s %s', $metric->getName(), $metric->getHelp()) . PHP_EOL;
            $result .= sprintf('# TYPE %s %s', $metric->getName(), $metric->getType()) . PHP_EOL;
            $result .= sprintf('%s %d', $metric->getName(), $metric->getValue()) . PHP_EOL;
        }

        return $this->serializer->serializeText($response, $result);
    }
}
