<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\Filter;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Scalar\DateTimeType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;

class EventFilterType extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'occurredAfter' => [
                        'type' => TypeRegistry::get(DateTimeType::class)
                    ],
                    'occurredBefore' => [
                        'type' => TypeRegistry::get(DateTimeType::class)
                    ],
                    'types' => [
                        'type' => Type::listOf(Type::string())
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
