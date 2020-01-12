<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

interface QueryTypeInterface
{
    public function getQueries(): array;
}