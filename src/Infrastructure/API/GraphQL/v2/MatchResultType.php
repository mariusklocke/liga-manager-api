<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class MatchResultType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => function () {
                return [
                    'homeScore' => [
                        'type' => Type::nonNull(Type::int())
                    ],
                    'guestScore' => [
                        'type' => Type::nonNull(Type::int())
                    ]
                ];
            }
        ]);
    }
}
