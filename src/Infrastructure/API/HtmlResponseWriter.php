<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Psr\Http\Message\ResponseInterface;

class HtmlResponseWriter
{
    public function write(ResponseInterface $response, $data): ResponseInterface
    {
        $response->getBody()->write($data);

        return $response->withHeader('Content-Type', 'text/html');
    }
}