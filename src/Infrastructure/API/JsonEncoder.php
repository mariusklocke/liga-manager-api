<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Psr\Http\Message\ResponseInterface;

class JsonEncoder
{
    public function encode(ResponseInterface $response, $data): ResponseInterface
    {
        $response->getBody()->write(json_encode($data));

        return $response->withHeader('Content-Type', 'application/json');
    }
}