<?php

declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\MCP;

use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use HexagonalPlayground\Infrastructure\API\MCP\Tool\ToolInterface;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class Controller extends BaseController
{
    /** @var ToolInterface[] */
    private array $tools = [];

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param ToolInterface[] $tools
     */
    public function __construct(ResponseFactoryInterface $responseFactory, array $tools)
    {
        $this->responseFactory = $responseFactory;
        $this->tools = $tools;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws InvalidInputException
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $requestHeaders = [];
        foreach ($request->getHeaders() as $name => $values) {
            $requestHeaders[$name] = $values[0];
        }
        $requestBody = $this->parseJson($request);
        $requestBody['jsonrpc'] === '2.0' || throw new InvalidInputException('Unsupported JSON-RPC version');
        $requestBody['id'] !== null || throw new InvalidInputException('Request body must contain an "id" property');
        $requestBody['params'] ??= [];

        is_string($requestBody['params']['method']) || throw new InvalidInputException('Request body property at ".params.method" must be a string');
        $requestBody['params']['method'] === $requestHeaders['Mcp-Method'] || throw new InvalidInputException('Value for "Mcp-Method" header does not match value at ".params.method" in request body');

        switch ($requestBody['params']['method']) {
            case 'server/discover':
                return $this->discoverServer($requestBody['id']);
            case 'tools/call':
                return $this->callTool($requestBody, $requestHeaders);
            case 'tools/list':
                return $this->listTools($requestBody['id']);
        }

        throw new InvalidInputException('Unsupported method: ' . ($requestBody['params']['method']));
    }

    private function discoverServer(string|int $requestId): ResponseInterface
    {
        return $this->buildJsonResponse([
            'jsonrpc' => '2.0',
            'id' => $requestId,
            'result' => [
                'resultType' => 'complete',
                'supportedVersions' => ['2026-07-28'],
                'capabilities' => [
                    'tools' => new \stdClass()
                ],
                'ttlMs' => 60000,
                'cacheScope' => 'private'
            ]
        ]);
    }

    private function callTool(array $requestBody, array $requestHeaders): ResponseInterface
    {
        $requestBody['params']['name'] === $requestHeaders['Mcp-Name'] || throw new InvalidInputException('Value for "Mcp-Name" header does not match value at ".params.name" in request body');
        $tool = array_find($this->tools, fn(ToolInterface $tool) => $tool->name === $requestBody['params']['name']);
        $tool !== null || throw new InvalidInputException('Tool not found: ' . $requestBody['params']['name']);
        try {
            return $this->buildJsonResponse([
                'jsonrpc' => '2.0',
                'id' => $requestBody['id'],
                'result' => [
                    'resultType' => 'complete',
                    'structuredContent' => $tool->call($requestBody['params']['arguments'] ?? []),
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->buildErrorResponse($requestBody['id'], 'Error calling tool: ' . $e->getMessage(), 500);
        }
    }

    private function listTools(string|int $requestId): ResponseInterface
    {
        return $this->buildJsonResponse([
            'jsonrpc' => '2.0',
            'id' => $requestId,
            'result' => [
                'resultType' => 'complete',
                'tools' => $this->tools,
                'ttlMs' => 60000,
                'cacheScope' => 'private'
            ]
        ]);
    }

    private function buildErrorResponse(string|int $id, string $message, int $code): ResponseInterface
    {
        return $this->buildJsonResponse([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $code,
                'message' => $message
            ],
        ]);
    }
}