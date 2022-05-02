<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class MatchQuery extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'match',
            [
                'id',
                'homeTeam' => [
                    'id',
                    'name'
                ],
                'guestTeam' => [
                    'id',
                    'name'
                ],
                'pitch' => [
                    'id',
                    'label'
                ],
                'kickoff'
            ],
            [
                'id' => 'String!'
            ],
            $argValues
        );
    }
}
