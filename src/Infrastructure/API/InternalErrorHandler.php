<?php

namespace HexagonalPlayground\Infrastructure\API;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Throwable;

class InternalErrorHandler
{
    /** @var bool */
    private $exposeDetails;

    /**
     * @param bool $exposeDetails
     */
    public function __construct($exposeDetails = false)
    {
        $this->exposeDetails = $exposeDetails;
    }

    /**
     * Handles the given Throwable by returning a 500 Response
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Throwable $throwable
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, Throwable $throwable) : ResponseInterface
    {
        $response = new Response(500);
        $body = [
            'title' => 'Internal Server Error'
        ];
        if ($this->exposeDetails) {
            $body = array_merge($body, [
                'type' => get_class($throwable),
                'message' => $throwable->getMessage(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'stacktrace' => $throwable->getTrace(),
                'previous' => $throwable->getPrevious()->getMessage()
            ]);
        }

        return $response->withJson($body);
    }
}
