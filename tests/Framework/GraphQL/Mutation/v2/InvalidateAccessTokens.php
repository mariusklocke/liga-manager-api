<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\Mutation;

class InvalidateAccessTokens extends Mutation
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'invalidateAccessTokens',
            [
                'userId' => 'String!'
            ],
            $argValues
        );
    }
}
