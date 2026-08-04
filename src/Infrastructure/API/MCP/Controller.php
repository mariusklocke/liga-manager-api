<?php

declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\MCP;

use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use HexagonalPlayground\Infrastructure\API\MCP\Tool\ToolInterface;
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
     * @throws Exception
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $requestHeaders = [];
        foreach ($request->getHeaders() as $name => $values) {
            $requestHeaders[$name] = $values[0];
        }
        $requestBody = $this->parseJson($request);

        try {
            $this->validate($requestBody, $requestHeaders);
            switch ($requestBody['method']) {
                case 'server/discover':
                    return $this->discoverServer($requestBody['id']);
                case 'tools/call':
                    return $this->callTool($requestBody);
                case 'tools/list':
                    return $this->listTools($requestBody['id']);
                default:
                    throw new Exception('Unsupported method', Exception::METHOD_NOT_FOUND);
            }
        } catch (Exception $e) {
            return $this->buildErrorResponse($requestBody['id'] ?? '', $e->getMessage(), $e->getCode());
        }
    }

    private function validate(array $requestBody, array $requestHeaders): void
    {
        $requestBody['jsonrpc'] ?? throw new Exception('Request body must contain a "jsonrpc" property', Exception::INVALID_REQUEST);
        $requestBody['jsonrpc'] === '2.0' || throw new Exception('Unsupported JSON-RPC version', Exception::INVALID_REQUEST);
        $requestBody['id'] ?? throw new Exception('Request body must contain an "id" property', Exception::INVALID_REQUEST);
        is_string($requestBody['id']) || is_int($requestBody['id']) || throw new Exception('Request body property "id" must be string or integer', Exception::INVALID_REQUEST);
        $requestBody['method'] ?? throw new Exception('Request body must contain a "method" property', Exception::INVALID_REQUEST);
        is_string($requestBody['method']) || throw new Exception('Request body property at "method" must be a string', Exception::INVALID_REQUEST);
        $requestHeaders['Mcp-Method'] ?? throw new Exception('Request headers must contain a "Mcp-Method" header', Exception::INVALID_REQUEST);
        $requestBody['method'] === $requestHeaders['Mcp-Method'] || throw new Exception('Value for "Mcp-Method" header does not match value at "method" in request body', Exception::HEADER_MISMATCH);

        if ($requestBody['method'] === 'tools/call') {
            $requestBody['params'] ?? throw new Exception('Request body must contain a "params" property', Exception::INVALID_REQUEST);
            is_array($requestBody['params']) || throw new Exception('Request body property "params" must be an object', Exception::INVALID_REQUEST);
            $requestBody['params']['name'] ?? throw new Exception('Request body must contain a ".params.name" property', Exception::INVALID_REQUEST);
            is_string($requestBody['params']['name']) || throw new Exception('Request body property at ".params.name" must be a string', Exception::INVALID_REQUEST);
            $requestHeaders['Mcp-Name'] ?? throw new Exception('Request headers must contain a "Mcp-Name" header', Exception::INVALID_REQUEST);
            $requestBody['params']['name'] === $requestHeaders['Mcp-Name'] || throw new Exception('Value for "Mcp-Name" header does not match value at ".params.name" in request body', Exception::HEADER_MISMATCH);
        }
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

    private function callTool(array $requestBody): ResponseInterface
    {
        $tool = array_find($this->tools, fn(ToolInterface $tool) => $tool->name === $requestBody['params']['name']);
        $tool ?? throw new Exception('Tool not found', Exception::INVALID_PARAMS);
        
        return $this->buildJsonResponse([
            'jsonrpc' => '2.0',
            'id' => $requestBody['id'],
            'result' => [
                'resultType' => 'complete',
                'structuredContent' => $tool->call($requestBody['params']['arguments'] ?? []),
            ]
        ]);
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
        ], 400);
    }
}