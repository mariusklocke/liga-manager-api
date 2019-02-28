<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

interface CustomObjectType
{
    /**
     * @param mixed $value
     * @return object
     */
    public function parseCustomValue($value): object;
}