<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class Tournament extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'tournament',
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
                'id' => 'String!'
            ],
            $argValues
        );
    }
}
