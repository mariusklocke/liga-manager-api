<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Health;

use Exception;
use HexagonalPlayground\Infrastructure\API\JsonEncodingTrait;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Controller
{
    use JsonEncodingTrait;

    /** @var HealthCheckInterface[] */
    private $checks;

    /**
     * @param HealthCheckInterface[] $checks
     */
    public function __construct(array $checks)
    {
        $this->checks = $checks;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function health(RequestInterface $request, ResponseInterface $response): ResponseInterface
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

        return $this->toJson($response, $result);
    }
}