<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Value\TeamIdPair;

class TeamIdPairType extends InputObjectType implements CustomObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'home_team_id' => [
                        'type' => Type::string()
                    ],
                    'guest_team_id' => [
                        'type' => Type::string()
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }

    public function parseCustomValue($value): object
    {
        return TeamIdPair::fromArray($value);
    }
}