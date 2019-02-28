<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\InputObjectType;
use HexagonalPlayground\Application\InputParser;

class DatePeriodType extends InputObjectType implements CustomObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'from' => [
                        'type' => DateType::getInstance()
                    ],
                    'to' => [
                        'type' => DateType::getInstance()
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }

    public function parseCustomValue($value): object
    {
        return InputParser::parseDatePeriod($value);
    }
}
