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
        return $this->buildTextResponse($this->metricsStore->export());
    }
}
