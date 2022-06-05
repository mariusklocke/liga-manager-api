<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\Mutation;

class SendPasswordResetMail extends Mutation
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'sendPasswordResetMail',
            [
                'email' => 'String!',
                'targetPath' => 'String!'
            ],
            $argValues
        );
    }
}
