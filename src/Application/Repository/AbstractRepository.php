<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Repository;

use HexagonalPlayground\Application\ReadDbAdapterInterface;

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
