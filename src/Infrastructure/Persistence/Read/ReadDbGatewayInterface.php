<?php

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use Exception;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;
use Iterator;

interface ReadDbGatewayInterface
{
    /**
     * @param string $from
     * @param array $joins
     * @param iterable|Filter[] $filters
     * @param iterable|Sorting[] $sortings
     * @param null|Pagination $pagination
     * @return Iterator|array[]
     * @throws Exception
     */
    public function fetch(
        string      $from,
        array       $joins = [],
        iterable    $filters = [],
        iterable    $sortings = [],
        ?Pagination $pagination = null
    ): Iterator;
}
