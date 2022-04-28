<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class User extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'user',
            [
                'id',
                'email',
                'firstName',
                'lastName',
                'role',
                'teams' => [
                    'id',
                    'name'
                ]
            ],
            [
                'id' => 'String'
            ],
            $argValues
        );
    }
}
