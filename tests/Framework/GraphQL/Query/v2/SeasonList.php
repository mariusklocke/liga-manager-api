<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class SeasonList extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'seasonList',
            [
                'id',
                'name',
                'state',
                'matchDayCount',
                'teamCount',
                'matchDays' => [
                    'id',
                    'number',
                    'matches' => [
                        'id'
                    ]
                ],
                'teams' => [
                    'id',
                    'name'
                ],
                'ranking' => [
                    'updatedAt',
                    'positions' => [
                        'team' => [
                            'id'
                        ],
                        'sortIndex',
                        'number',
                        'matches',
                        'wins',
                        'draws',
                        'losses',
                        'scoredGoals',
                        'concededGoals',
                        'points'
                    ],
                    'penalties' => [
                        'id',
                        'team' => [
                            'id'
                        ],
                        'reason',
                        'createdAt',
                        'points'
                    ]
                ]
            ],
            [
                'filter' => 'SeasonFilter',
                'pagination' => 'Pagination'
            ],
            $argValues
        );
    }
}
