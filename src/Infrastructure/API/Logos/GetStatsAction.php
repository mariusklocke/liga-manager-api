<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonResponseWriter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetStatsAction implements ActionInterface
{
    private JsonResponseWriter $responseWriter;

    public function __construct(JsonResponseWriter $responseWriter)
    {
        $this->responseWriter = $responseWriter;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->responseWriter->write($response, [
            'maxFileSize' => 0,
            'totalFileSize' => 0,
            'totalFileCount' => 0
        ]);
    }
}
