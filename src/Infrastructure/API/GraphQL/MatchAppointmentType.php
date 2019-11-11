<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Value\MatchAppointment;

class MatchAppointmentType extends InputObjectType implements CustomObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'kickoff' => [
                        'type' => Type::nonNull(DateTimeType::getInstance())
                    ],
                    'unavailable_team_ids' => [
                        'type' => Type::nonNull(Type::listOf(Type::string()))
                    ],
                    'pitch_id' => [
                        'type' => Type::nonNull(Type::string())
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }

    /**
     * @param mixed $value
     * @return object
     */
    public function parseCustomValue($value): object
    {
        return new MatchAppointment(
            $value['kickoff'],
            $value['unavailable_team_ids'],
            $value['pitch_id']
        );
    }
}