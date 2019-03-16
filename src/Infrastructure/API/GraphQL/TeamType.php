<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class TeamType extends ObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'name' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'created_at' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'contact' => [
                        'type' => ContactType::getInstance()
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
