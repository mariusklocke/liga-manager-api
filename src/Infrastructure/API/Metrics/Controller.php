<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller extends BaseController
{
    private StoreInterface $metricsStore;

    public function __construct(ResponseFactoryInterface $responseFactory, StoreInterface $metricsStore)
    {
        parent::__construct($responseFactory);
        $this->metricsStore = $metricsStore;
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $result = '';

        foreach ($this->metricsStore->getMetrics() as $metric) {
            $result .= sprintf('# HELP %s %s', $metric->getName(), $metric->getHelp()) . PHP_EOL;
            $result .= sprintf('# TYPE %s %s', $metric->getName(), $metric->getType()) . PHP_EOL;
            $result .= sprintf('%s %d', $metric->getName(), $metric->getValue()) . PHP_EOL;
        }

        return $this->buildTextResponse($result);
    }
}
