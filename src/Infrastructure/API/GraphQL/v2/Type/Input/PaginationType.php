<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class PaginationType extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'limit' => [
                        'type' => Type::nonNull(Type::int())
                    ],
                    'offset' => [
                        'type' => Type::nonNull(Type::int())
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
