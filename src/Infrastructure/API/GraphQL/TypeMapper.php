<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\Type;

class TypeMapper
{
    public function map(string $phpType): Type
    {
        $isNullable = false;
        if (strpos($phpType, '|null') > 0) {
            $phpType = str_replace('|null', '', $phpType);
            $isNullable = true;
        }

        $isCollection = false;
        if (strpos($phpType, '[]') > 0) {
            $phpType = str_replace('[]', '', $phpType);
            $isCollection = true;
        }

        $innerType = $this->mapInnerType($phpType);
        switch (true) {
            case !$isNullable && $isCollection:
                return Type::nonNull(Type::listOf($innerType));
            case $isCollection:
                return Type::listOf($innerType);
            case !$isNullable:
                return Type::nonNull($innerType);
        }

        return $innerType;
    }

    private function mapInnerType(string $phpType): Type
    {
        switch ($phpType) {
            case 'string':
                return Type::string();
            case 'int':
            case 'integer':
                return Type::int();
            case 'DatePeriod':
                return DatePeriodType::getInstance();
            case 'float':
            case 'float|int':
                return Type::float();
            case 'TeamIdPair':
                return TeamIdPairType::getInstance();
        }

        throw new MappingException(sprintf('Cannot map internal type "%s"', $phpType));
    }
}