<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Index;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use HexagonalPlayground\Infrastructure\API\Controller as BaseController;

class Controller extends BaseController
{
    private string $appVersion;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param string $appVersion
     */
    public function __construct(ResponseFactoryInterface $responseFactory, string $appVersion)
    {
        parent::__construct($responseFactory);
        $this->appVersion = $appVersion;
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->buildJsonResponse([
            'limits' => [
                'logos' => [
                    'size' => '2M',
                    'types' => ['image/webp']
                ],
                'requests' => '5r/s'
            ],
            'version' => $this->appVersion
        ]);
    }
}
