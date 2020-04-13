<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Health;

use Exception;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonEncodingTrait;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class QueryAction implements ActionInterface
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

        return $this->toJson($response, $result);
    }
}