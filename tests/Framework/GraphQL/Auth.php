<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL;

interface Auth
{
    public function encode(): string;
}
