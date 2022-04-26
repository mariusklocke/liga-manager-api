<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Domain\Value\MatchAppointment;
use HexagonalPlayground\Infrastructure\API\GraphQL\CustomObjectType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Scalar\DateTimeType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;

class MatchAppointmentType extends InputObjectType implements CustomObjectType
{
    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'kickoff' => [
                        'type' => Type::nonNull(TypeRegistry::get(DateTimeType::class))
                    ],
                    'unavailableTeamIds' => [
                        'type' => Type::nonNull(Type::listOf(Type::string()))
                    ],
                    'pitchId' => [
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
            $value['unavailableTeamIds'],
            $value['pitchId']
        );
    }
}
