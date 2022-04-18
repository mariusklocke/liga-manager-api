<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Criteria\Filter;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\SeasonStateType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;

class SeasonFilterType extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'states' => [
                        'type' => Type::listOf(TypeRegistry::get(SeasonStateType::class))
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
