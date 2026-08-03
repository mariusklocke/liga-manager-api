<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\MCP;

use HexagonalPlayground\Tests\Framework\DataGenerator;
use HexagonalPlayground\Tests\Framework\HttpTest;

class McpTest extends HttpTest
{
    public function testToolsCanBeListed(): void
    {
        $requestId = DataGenerator::generateId();
        $request = $this->createRequest('POST', '/api/mcp', [
            'jsonrpc' => '2.0',
            'id' => $requestId,
            'params' => [
                'method' => 'tools/list'
            ]
        ]);
        $request = $request->withHeader('Mcp-Method', 'tools/list');
        $response = $this->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());
        $parsedBody = $this->parser->parse($response);

        self::assertObjectHasProperty('id', $parsedBody);
        self::assertObjectHasProperty('jsonrpc', $parsedBody);
        self::assertObjectHasProperty('result', $parsedBody);

        self::assertSame($requestId, $parsedBody->id);
        self::assertSame('2.0', $parsedBody->jsonrpc);
        self::assertIsObject($parsedBody->result);
        self::assertIsArray($parsedBody->result->tools);
        self::assertGreaterThan(0, count($parsedBody->result->tools));
        foreach ($parsedBody->result->tools as $tool) {
            self::assertObjectHasProperty('name', $tool);
            self::assertObjectHasProperty('description', $tool);
        }
        self::assertGreaterThan(1000, $parsedBody->result->ttlMs);
        self::assertSame('private', $parsedBody->result->cacheScope);

        $expectedTools = ['list_teams'];
        foreach ($expectedTools as $expectedTool) {
            self::assertContains($expectedTool, array_column($parsedBody->result->tools, 'name'));
        }
    }

    public function testToolsCanBeCalled(): void
    {
        $requestId = DataGenerator::generateId();
        $request = $this->createRequest('POST', '/api/mcp', [
            'jsonrpc' => '2.0',
            'id' => $requestId,
            'params' => [
                'method' => 'tools/call',
                'name' => 'list_teams'
            ]
        ]);
        $request = $request->withHeader('Mcp-Method', 'tools/call');
        $request = $request->withHeader('Mcp-Name', 'list_teams');
        $response = $this->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());
        $parsedBody = $this->parser->parse($response);

        self::assertObjectHasProperty('id', $parsedBody);
        self::assertObjectHasProperty('jsonrpc', $parsedBody);
        self::assertObjectHasProperty('result', $parsedBody);

        self::assertObjectHasProperty('structuredContent', $parsedBody->result);

        foreach ($parsedBody->result->structuredContent as $team) {
            self::assertObjectHasProperty('id', $team);
            self::assertObjectHasProperty('name', $team);
            self::assertObjectHasProperty('created_at', $team);
        }
    }

    public function testServerCanBeDiscovered(): void
    {
        $requestId = DataGenerator::generateId();
        $request = $this->createRequest('POST', '/api/mcp', [
            'jsonrpc' => '2.0',
            'id' => $requestId,
            'params' => [
                'method' => 'server/discover'
            ]
        ]);
        $request = $request->withHeader('Mcp-Method', 'server/discover');
        $response = $this->sendRequest($request);
        self::assertSame(200, $response->getStatusCode());
        $parsedBody = $this->parser->parse($response);

        self::assertObjectHasProperty('id', $parsedBody);
        self::assertObjectHasProperty('jsonrpc', $parsedBody);
        self::assertObjectHasProperty('result', $parsedBody);

        self::assertObjectHasProperty('capabilities', $parsedBody->result);
        self::assertObjectHasProperty('supportedVersions', $parsedBody->result);
        self::assertObjectHasProperty('ttlMs', $parsedBody->result);
        self::assertObjectHasProperty('cacheScope', $parsedBody->result);

        self::assertObjectHasProperty('tools', $parsedBody->result->capabilities);
        self::assertContains('2026-07-28', $parsedBody->result->supportedVersions);
        self::assertGreaterThan(1000, $parsedBody->result->ttlMs);
        self::assertSame('private', $parsedBody->result->cacheScope);
    }
}
