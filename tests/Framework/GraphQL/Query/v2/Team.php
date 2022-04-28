<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class Team extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'team',
            [
                'id',
                'name',
                'contact' => [
                    'firstName',
                    'lastName',
                    'phone',
                    'email'
                ]
            ],
            [
                'id' => 'String!'
            ],
            $argValues
        );
    }
}
