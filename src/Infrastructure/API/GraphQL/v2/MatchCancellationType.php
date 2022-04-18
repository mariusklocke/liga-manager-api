<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class MatchCancellationType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => function () {
                return [
                    'createdAt' => [
                        'type' => Type::nonNull(TypeRegistry::get(DateTimeType::class))
                    ],
                    'reason' => [
                        'type' => Type::nonNull(Type::string())
                    ]
                ];
            }
        ]);
    }
}
