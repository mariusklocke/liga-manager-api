<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\Type;

interface TypeMapperInterface
{
    public function map(string $phpType): Type;
}
