<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Application\Exception\NotFoundException;

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
     * @param string $message Exception message to use if no row could be found
     * @return array
     * @throws NotFoundException if there was no row found
     */
    public function fetchFirstRow(string $query, array $params, string $message): array;

    /**
     * Fetch the first column of the first row
     *
     * @param string $query
     * @param array  $params
     * @return mixed
     */
    public function fetchSingleColumn(string $query, array $params = []);
}