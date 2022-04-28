<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class UserList extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'userList',
            [
                'id',
                'email',
                'role',
                'firstName',
                'lastName',
                'teams' => [
                    'id',
                    'name'
                ]
            ],
            [
                'pagination' => 'Pagination'
            ],
            $argValues
        );
    }
}
