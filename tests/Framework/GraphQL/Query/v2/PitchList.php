<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class PitchList extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'pitchList',
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
                ],
                'matches' => [
                    'id'
                ]
            ],
            [
                'filter' => 'PitchFilter',
                'pagination' => 'Pagination'
            ],
            $argValues
        );
    }
}
