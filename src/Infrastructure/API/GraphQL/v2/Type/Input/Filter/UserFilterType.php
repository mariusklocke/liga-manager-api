<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\Filter;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Enum\UserRoleType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;

class UserFilterType extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'roles' => [
                        'type' => Type::listOf(TypeRegistry::get(UserRoleType::class))
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
