<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class TeamList extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'teamList',
            [
                'id',
                'name',
                'createdAt',
                'contact' => [
                    'firstName',
                    'lastName',
                    'phone',
                    'email'
                ],
                'users' => [
                    'id',
                    'email'
                ],
                'homeMatches' => [
                    'id'
                ],
                'guestMatches' => [
                    'id'
                ]
            ],
            [
                'filter' => 'TeamFilter',
                'pagination' => 'Pagination'
            ],
            $argValues
        );
    }
}
