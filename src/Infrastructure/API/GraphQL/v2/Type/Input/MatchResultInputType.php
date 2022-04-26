<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Domain\Value\MatchResult;
use HexagonalPlayground\Infrastructure\API\GraphQL\CustomObjectType;

class MatchResultInputType extends InputObjectType implements CustomObjectType
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

    public function parseCustomValue($value): object
    {
        return new MatchResult($value['homeScore'], $value['guestScore']);
    }
}
