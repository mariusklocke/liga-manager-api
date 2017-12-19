<?php

namespace HexagonalDream\Application\Repository;

use HexagonalDream\Application\ReadDbAdapterInterface;

class AbstractRepository
{
    /** @var ReadDbAdapterInterface */
    private $db;

    public function __construct(ReadDbAdapterInterface $readDbAdapter)
    {
        $this->db = $readDbAdapter;
    }

    protected function getDb()
    {
        return $this->db;
    }
}
