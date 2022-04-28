<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\Mutation;

class DeleteSeason extends Mutation
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'deleteSeason',
            [
                'id' => 'String!'
            ],
            $argValues
        );
    }
}
