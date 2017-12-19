<?php

namespace HexagonalDream\Application;

interface ReadDbAdapterInterface
{
    /**
     * @param string $query
     * @param array $params
     * @return array
     */
    public function fetchAll(string $query, array $params = []);
}