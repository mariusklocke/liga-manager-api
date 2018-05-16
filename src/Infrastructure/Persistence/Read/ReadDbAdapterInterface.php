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
    public function fetchAll(string $query, array $params = []);

    /**
     * Fetch the first row as associative array
     *
     * @param string $query
     * @param array  $params
     * @return array|null
     */
    public function fetchFirstRow(string $query, array $params = []);

    /**
     * Fetch the first column of the first row
     *
     * @param string $query
     * @param array  $params
     * @return mixed
     */
    public function fetchSingleColumn(string $query, array $params = []);
}