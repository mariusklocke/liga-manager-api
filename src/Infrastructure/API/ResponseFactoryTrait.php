<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;

trait ResponseFactoryTrait
{
    /**
     * @param int   $status
     * @param mixed $data
     * @return ResponseInterface
     */
    protected function createResponse(int $status, $data = null): ResponseInterface
    {
        $response = new Response($status);
        return $data !== null ? $response->withJson($data) : $response;
    }
}