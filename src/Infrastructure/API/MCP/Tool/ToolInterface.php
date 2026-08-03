<?php

declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\MCP\Tool;

interface ToolInterface
{
    public string $name { get; }
    public string $description { get; }
    public array $inputSchema { get; }
    public function call(array $params): array;
}