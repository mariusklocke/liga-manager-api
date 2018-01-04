<?php

namespace HexagonalDream\Infrastructure\API;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;

class MethodNotAllowedHandler
{
    /**
     * Handles an error where the requested HTTP method is not allowed by returning a 405 Response
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param string[] $allowedMethods
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $allowedMethods) : ResponseInterface
    {
        $response = new Response(405);
        return $response->withJson(['title' => 'Method Not Allowed', 'allowedMethods' => $allowedMethods]);
    }
}
