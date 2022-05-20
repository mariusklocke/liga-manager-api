<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\Mutation;

class CreateRankingPenalty extends Mutation
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'createRankingPenalty',
            [
                'id' => 'String!',
                'seasonId' => 'String!',
                'teamId' => 'String!',
                'reason' => 'String!',
                'points' => 'Int!'
            ],
            $argValues
        );
    }
}
