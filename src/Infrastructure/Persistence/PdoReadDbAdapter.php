<?php

namespace HexagonalDream\Infrastructure\Persistence;

use HexagonalDream\Application\ReadDbAdapterInterface;
use PDO;

class PdoReadDbAdapter implements ReadDbAdapterInterface
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function fetchAll(string $query, array $params = [])
    {
        $statement = $this->pdo->prepare($query);
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $key = ':' . $key;
            }
            $statement->bindValue($key, $value, $this->getParamType($value));
        }
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $value
     * @return int
     */
    private function getParamType($value)
    {
        $valueType = strtolower(gettype($value));
        $paramTypeMap = [
            'integer' => PDO::PARAM_INT,
            'string' => PDO::PARAM_STR,
            'null' => PDO::PARAM_NULL
        ];
        return isset($paramTypeMap[$valueType]) ? $paramTypeMap[$valueType] : PDO::PARAM_STR;
    }
}
