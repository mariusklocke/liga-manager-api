<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

interface ReadDbAdapterInterface
{
    /**
     * Fetch all rows as array of associative arrays
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function fetchAll(string $query, array $params = []): array;

    /**
     * Fetch the first row as associative array
     *
     * @param string $query
     * @param array  $params
     * @return array|null
     */
    public function fetchFirstRow(string $query, array $params): ?array;
}
