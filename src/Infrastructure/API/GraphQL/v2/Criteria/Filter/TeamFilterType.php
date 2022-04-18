<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Criteria\Filter;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class TeamFilterType extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'namePattern' => [
                        'type' => Type::string()
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
