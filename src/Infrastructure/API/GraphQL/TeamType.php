<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class TeamType extends ObjectType
{
    use SingletonTrait;

    const NAME = 'Team';

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Type::string()
                    ],
                    'name' => [
                        'type' => Type::string()
                    ],
                    'created_at' => [
                        'type' => Type::string() // look for date-time
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
