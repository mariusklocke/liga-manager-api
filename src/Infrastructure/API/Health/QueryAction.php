<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Health;

use Exception;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonResponseWriter;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class QueryAction implements ActionInterface
{
    /** @var HealthCheckInterface[] */
    private array $checks;

    /** @var JsonResponseWriter */
    private JsonResponseWriter $responseWriter;

    /**
     * @param HealthCheckInterface[] $checks
     * @param JsonResponseWriter $responseWriter
     */
    public function __construct(array $checks, JsonResponseWriter $responseWriter)
    {
        $this->checks = $checks;
        $this->responseWriter = $responseWriter;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $result = [];

        foreach ($this->checks as $check) {
            try {
                $check();
                $result[$check->getName()] = 'OK';
            } catch (Exception $e) {
                $result[$check->getName()] = 'Failed';
                $response = $response->withStatus(500);
            }
        }

        return $this->responseWriter->write($response, $result);
    }
}
