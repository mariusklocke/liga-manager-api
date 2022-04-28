<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;

class EventList extends Query
{
    public function __construct(array $argValues = [])
    {
        parent::__construct(
            'eventList',
            [
                'id',
                'occurredAt',
                'type'
            ],
            [
                'filter' => 'EventFilter',
                'pagination' => 'Pagination'
            ],
            $argValues
        );
    }
}
