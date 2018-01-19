<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;

class NotFoundErrorHandler
{
    /**
     * Handles an error where a resource cannot be found by returning a 404 Response
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $response = new Response(404);
        return $response->withJson(['title' => 'Not Found']);
    }
}
