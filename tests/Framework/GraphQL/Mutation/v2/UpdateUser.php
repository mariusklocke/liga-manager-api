<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\Mutation;

class UpdateUser extends Mutation
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'updateUser',
            [
                'id' => 'String!',
                'email' => 'String!',
                'firstName' => 'String!',
                'lastName' => 'String!',
                'role' => 'String!',
                'teamIds' => '[String]!'
            ],
            $argValues
        );
    }
}
