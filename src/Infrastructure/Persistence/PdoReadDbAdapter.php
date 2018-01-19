<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Application\ReadDbAdapterInterface;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class PdoReadDbAdapter implements ReadDbAdapterInterface
{
    /** @var PDO */
    private $pdo;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function fetchAll(string $query, array $params = [])
    {
        $statement = $this->executeQuery($query, $params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $query
     * @param array  $params
     * @return array|null
     */
    public function fetchFirstRow(string $query, array $params = [])
    {
        $statement = $this->executeQuery($query, $params);
        $firstRow = $statement->fetch(PDO::FETCH_ASSOC);
        return is_array($firstRow) ? $firstRow : null;
    }

    /**
     * @param string $query
     * @param array  $params
     * @return mixed
     */
    public function fetchSingleColumn(string $query, array $params = [])
    {
        $statement = $this->executeQuery($query, $params);
        $value = $statement->fetchColumn();
        return ($value !== false) ? $value : null;
    }

    /**
     * @param PDOStatement $statement
     * @param array        $params
     */
    private function bindParameters(PDOStatement $statement, array $params)
    {
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $key = ':' . $key;
            }
            $statement->bindValue($key, $value, $this->getParamType($value));
        }
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

    /**
     * @param string $query
     * @param array $params
     * @return PDOStatement
     */
    private function executeQuery(string $query, array $params)
    {
        $statement = $this->pdo->prepare($query);
        if (!empty($params)) {
            $this->bindParameters($statement, $params);
        }
        if ($this->logger !== null) {
            $this->logger->info('Executing query ' . $query);
            if (count($params) > 0) {
                $this->logger->info('With Parameters ' . print_r($params, true));
            }
        }
        $statement->execute();
        return $statement;
    }

    /**
     * Registers a Logger to use for logging all queries
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
