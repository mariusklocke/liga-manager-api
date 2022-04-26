<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Domain\Value\GeographicLocation;
use HexagonalPlayground\Infrastructure\API\GraphQL\CustomObjectType;

class GeoLocationInputType extends InputObjectType implements CustomObjectType
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

    public function parseCustomValue($value): object
    {
        return new GeographicLocation($value['longitude'], $value['latitude']);
    }
}
