<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class GeoLocationType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => function () {
                return [
                    'latitude' => [
                        'type' => Type::nonNull(Type::float())
                    ],
                    'longitude' => [
                        'type' => Type::nonNull(Type::float())
                    ]
                ];
            }
        ]);
    }
}
