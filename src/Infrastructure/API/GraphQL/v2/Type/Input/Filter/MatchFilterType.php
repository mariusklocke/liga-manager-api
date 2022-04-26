<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\Filter;

use GraphQL\Type\Definition\InputObjectType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Scalar\DateTimeType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;

class MatchFilterType extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'kickoffAfter' => [
                        'type' => TypeRegistry::get(DateTimeType::class)
                    ],
                    'kickoffBefore' => [
                        'type' => TypeRegistry::get(DateTimeType::class)
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
