<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Index;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use HexagonalPlayground\Infrastructure\API\Limits;

class Controller extends BaseController
{
    private Limits $limits;
    private string $appVersion;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param Limits $limits
     * @param string $appVersion
     */
    public function __construct(ResponseFactoryInterface $responseFactory, Limits $limits, string $appVersion)
    {
        parent::__construct($responseFactory);
        $this->limits = $limits;
        $this->appVersion = $appVersion;
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->buildJsonResponse([
            'allowed_file_types' => $this->limits->uploadFileTypes,
            'max_file_size' => $this->limits->uploadFileSize,
            'max_requests' => $this->limits->requestsPerSecond,
            'version' => $this->appVersion
        ]);
    }
}
