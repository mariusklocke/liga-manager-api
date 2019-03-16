<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class PitchType extends ObjectType
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
                    'label' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'location_longitude' => [
                        'type' => Type::nonNull(Type::float())
                    ],
                    'location_latitude' => [
                        'type' => Type::nonNull(Type::float())
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