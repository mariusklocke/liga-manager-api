<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Interfaces\Http\HeadersInterface;

trait ResponseFactoryTrait
{
    /**
     * @param int                   $status
     * @param mixed                 $data
     * @param HeadersInterface|null $headers
     * @return ResponseInterface
     */
    protected function createResponse(int $status, $data = null, HeadersInterface $headers = null): ResponseInterface
    {
        $response = new Response($status, $headers);
        return $data !== null ? $response->withJson($data) : $response;
    }
}