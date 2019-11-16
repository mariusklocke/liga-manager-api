<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Domain\Value\DatePeriod;

class DatePeriodType extends InputObjectType implements CustomObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'from' => [
                        'type' => Type::nonNull(DateType::getInstance())
                    ],
                    'to' => [
                        'type' => Type::nonNull(DateType::getInstance())
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }

    public function parseCustomValue($value): object
    {
        return new DatePeriod($value['from'], $value['to']);
    }
}
