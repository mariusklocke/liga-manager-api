<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ContactType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => function () {
                return [
                    'firstName' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'lastName' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'phone' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'email' => [
                        'type' => Type::nonNull(Type::string())
                    ]
                ];
            }
        ]);
    }
}
