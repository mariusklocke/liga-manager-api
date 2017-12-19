<?php

namespace HexagonalDream\Framework\Persistence;

use Doctrine\DBAL\Connection;
use HexagonalDream\Application\ReadDbAdapterInterface;

class DoctrineReadDbAdapter implements ReadDbAdapterInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchAll(string $query, array $params = [])
    {
        return $this->connection->fetchAll($query, $params);
    }
}
