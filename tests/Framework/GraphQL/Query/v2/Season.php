<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class Season extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'season',
            [
                'id',
                'name',
                'state',
                'teams' => [
                    'id',
                    'name'
                ],
                'matchDays' => [
                    'id',
                    'number',
                    'matches' => [
                        'id'
                    ]
                ],
                'ranking' => [
                    'updatedAt',
                    'positions' => [
                        'team' => [
                            'id'
                        ]
                    ]
                ]
            ],
            [
                'id' => 'String!'
            ],
            $argValues
        );
    }
}
