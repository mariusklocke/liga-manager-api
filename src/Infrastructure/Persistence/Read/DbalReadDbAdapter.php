<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class DbalReadDbAdapter implements ReadDbAdapterInterface
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchAll(string $query, array $params = []): array
    {
        $types = array_map([$this, 'getParamType'], $params);

        return $this->connection->fetchAllAssociative($query, $params, $types);
    }

    public function fetchFirstRow(string $query, array $params): ?array
    {
        $types = array_map([$this, 'getParamType'], $params);

        $row = $this->connection->fetchAssociative($query, $params, $types);

        return is_array($row) ? $row : null;
    }

    public function fetchSingleColumn(string $query, array $params = [])
    {
        $types = array_map([$this, 'getParamType'], $params);

        $value = $this->connection->fetchOne($query, $params, $types);

        return $value !== false ? $value : null;
    }

    /**
     * Determines the DBAL parameter type for a value
     *
     * @param mixed $value
     * @return string
     */
    private function getParamType($value) : string
    {
        $valueType = strtolower(gettype($value));

        switch ($valueType) {
            case 'integer':
                return Types::INTEGER;
            case 'double':
                return Types::FLOAT;
            case 'string':
            default:
                return Types::STRING;
        }
    }
}
