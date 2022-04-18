<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\Type;

class TypeRegistry
{
    private static array $types;

    /**
     * @param string $class
     * @return Type|NullableType
     */
    public static function get(string $class): Type
    {
        if (!isset(self::$types[$class])) {
            self::$types[$class] = new $class;
        }

        return self::$types[$class];
    }
}
