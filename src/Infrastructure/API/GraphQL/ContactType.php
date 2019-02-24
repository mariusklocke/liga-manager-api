<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ContactType extends ObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'first_name' => [
                        'type' => Type::string()
                    ],
                    'last_name' => [
                        'type' => Type::string()
                    ],
                    'phone' => [
                        'type' => Type::string()
                    ],
                    'email' => [
                        'type' => Type::string()
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}