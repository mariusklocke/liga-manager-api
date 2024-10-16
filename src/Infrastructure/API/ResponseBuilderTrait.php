<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

trait ResponseBuilderTrait
{
    protected ResponseFactoryInterface $responseFactory;

    protected function buildJsonResponse(mixed $data, int $status = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status);
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json');
    }

    protected function buildTextResponse(string $data, int $status = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status);
        $response->getBody()->write($data);

        return $response->withHeader('Content-Type', 'text/plain');
    }

    protected function buildHtmlResponse(string $data, int $status = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status);
        $response->getBody()->write($data);

        return $response->withHeader('Content-Type', 'text/html');
    }

    protected function buildRedirectResponse(string $location, int $status = 302): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status);

        return $response->withHeader('Location', $location);
    }

    protected function buildResponse(int $status = 204): ResponseInterface
    {
        return $this->responseFactory->createResponse($status);
    }
}
