<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Health;

use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class Controller extends BaseController
{
    /** @var HealthCheckInterface[] */
    private array $checks;
    private string $appVersion;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param HealthCheckInterface[] $checks
     * @param string $appVersion
     */
    public function __construct(ResponseFactoryInterface $responseFactory, array $checks, string $appVersion)
    {
        parent::__construct($responseFactory);
        $this->checks = $checks;
        $this->appVersion = $appVersion;
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $status = 200;
        $result = [
            'version' => $this->appVersion,
            'checks' => []
        ];

        foreach ($this->checks as $check) {
            try {
                $check();
                $result['checks'][$check->getName()] = 'OK';
            } catch (Throwable $e) {
                $result['checks'][$check->getName()] = 'Failed';
                $status = 500;
            }
        }

        return $this->buildJsonResponse($result, $status);
    }
}
