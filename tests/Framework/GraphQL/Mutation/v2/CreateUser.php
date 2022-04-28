<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\Mutation;

class CreateUser extends Mutation
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'createUser',
            [
                'id' => 'String!',
                'email' => 'String!',
                'password' => 'String!',
                'firstName' => 'String!',
                'lastName' => 'String!',
                'role' => 'String!',
                'teamIds' => '[String]!'
            ],
            $argValues
        );
    }
}
