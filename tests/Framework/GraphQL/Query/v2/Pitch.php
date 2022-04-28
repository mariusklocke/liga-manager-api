<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class Pitch extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'pitch',
            [
                'id',
                'label',
                'location' => [
                    'latitude',
                    'longitude'
                ],
                'contact' => [
                    'firstName',
                    'lastName',
                    'phone',
                    'email'
                ]
            ],
            [
                'id' => 'String!'
            ],
            $argValues
        );
    }
}
