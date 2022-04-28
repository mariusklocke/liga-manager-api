<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class TournamentList extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'tournamentList',
            [
                'id',
                'name',
                'matchDays' => [
                    'id',
                    'number',
                    'matches' => [
                        'id'
                    ]
                ]
            ],
            [
                'pagination' => 'Pagination'
            ],
            $argValues
        );
    }
}
