<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Scalar\DateTimeType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;

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
