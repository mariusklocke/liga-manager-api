<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Psr\Http\Message\ResponseInterface;

trait JsonEncodingTrait
{
    /**
     * @param ResponseInterface $response
     * @param mixed $data
     * @return ResponseInterface
     */
    public function toJson(ResponseInterface $response, $data): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json');
    }
}