<?php

declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\MCP;

class Exception extends \Exception
{
    public const HEADER_MISMATCH = -32020;
    public const INVALID_PARAMS = -32602;
    public const INVALID_REQUEST = -32600;
    public const METHOD_NOT_FOUND = -32601;

    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}