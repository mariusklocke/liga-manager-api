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
    private JsonResponseWriter $responseWriter;
    private string $appVersion;

    /**
     * @param HealthCheckInterface[] $checks
     * @param JsonResponseWriter $responseWriter
     * @param string $appVersion
     */
    public function __construct(array $checks, JsonResponseWriter $responseWriter, string $appVersion)
    {
        $this->checks = $checks;
        $this->responseWriter = $responseWriter;
        $this->appVersion = $appVersion;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $result = [
            'version' => $this->appVersion,
            'checks' => []
        ];

        foreach ($this->checks as $check) {
            try {
                $check();
                $result['checks'][$check->getName()] = 'OK';
            } catch (Exception $e) {
                $result['checks'][$check->getName()] = 'Failed';
                $response = $response->withStatus(500);
            }
        }

        return $this->responseWriter->write($response, $result);
    }
}
