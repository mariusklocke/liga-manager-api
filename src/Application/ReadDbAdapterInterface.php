<?php

namespace HexagonalPlayground\Application;

interface ReadDbAdapterInterface
{
    /**
     * @param string $query
     * @param array $params
     * @return array
     */
    public function fetchAll(string $query, array $params = []);

    /**
     * @param string $query
     * @param array  $params
     * @return array|null
     */
    public function fetchFirstRow(string $query, array $params = []);

    /**
     * @param string $query
     * @param array  $params
     * @return mixed
     */
    public function fetchSingleColumn(string $query, array $params = []);
}