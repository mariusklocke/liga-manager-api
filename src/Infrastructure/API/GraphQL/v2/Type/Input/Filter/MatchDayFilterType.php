<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\Filter;

use GraphQL\Type\Definition\InputObjectType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Scalar\DateType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;

class MatchDayFilterType extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'startsAfter' => [
                        'type' => TypeRegistry::get(DateType::class)
                    ],
                    'startsBefore' => [
                        'type' => TypeRegistry::get(DateType::class)
                    ],
                    'endsAfter' => [
                        'type' => TypeRegistry::get(DateType::class)
                    ],
                    'endsBefore' => [
                        'type' => TypeRegistry::get(DateType::class)
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
