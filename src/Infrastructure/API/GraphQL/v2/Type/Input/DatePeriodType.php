<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Domain\Value\DatePeriod;
use HexagonalPlayground\Infrastructure\API\GraphQL\CustomObjectType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Scalar\DateType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;

class DatePeriodType extends InputObjectType implements CustomObjectType
{
    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'from' => [
                        'type' => Type::nonNull(TypeRegistry::get(DateType::class))
                    ],
                    'to' => [
                        'type' => Type::nonNull(TypeRegistry::get(DateType::class))
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
