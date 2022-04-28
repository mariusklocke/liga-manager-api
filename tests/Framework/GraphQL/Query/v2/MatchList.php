<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class MatchList extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'matchList',
            [
                'id',
                'matchDay' => [
                    'id'
                ],
                'homeTeam' => [
                    'id',
                    'name'
                ],
                'guestTeam' => [
                    'id',
                    'name'
                ],
                'kickoff',
                'pitch' => [
                    'id',
                    'label'
                ],
                'result' => [
                    'homeScore',
                    'guestScore'
                ],
                'cancellation' => [
                    'createdAt',
                    'reason'
                ]
            ],
            [
                'filter' => 'MatchFilter'
            ],
            $argValues
        );
    }
}
