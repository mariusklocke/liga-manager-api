<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class MatchDayList extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'matchDayList',
            [
                'id',
                'number',
                'startDate',
                'endDate',
            ],
            [
                'filter' => 'MatchDayFilter'
            ],
            $argValues
        );
    }
}
