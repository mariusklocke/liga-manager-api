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
                        'type' => Type::string()
                    ],
                    'label' => [
                        'type' => Type::string()
                    ],
                    'location_longitude' => [
                        'type' => Type::float()
                    ],
                    'location_latitude' => [
                        'type' => Type::float()
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