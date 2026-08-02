<?php

declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\MCP;

interface Tool
{
    public function call(array $params): array;
}