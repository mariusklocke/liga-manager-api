<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\InputObjectType;

class DatePeriodType extends InputObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function() {
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
}