<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use DateTimeInterface;
use Psr\Log\LoggerInterface;
use SQLite3;
use SQLite3Result;
use SQLite3Stmt;

class SqliteReadDbAdapter implements ReadDbAdapterInterface
{
    /** @var SQLite3 */
    private $sqlite;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(SQLite3 $sqlite)
    {
        $this->sqlite = $sqlite;
    }

    /**
     * @param string $query
     * @param array  $params
     * @return array
     */
    public function fetchAll(string $query, array $params = [])
    {
        $result = $this->executeQuery($query, $params);
        $data = [];
        while (($row = $result->fetchArray(SQLITE3_ASSOC)) !== false) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * @param string $query
     * @param array  $params
     * @return array|null
     */
    public function fetchFirstRow(string $query, array $params = [])
    {
        $result = $this->executeQuery($query, $params);
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return is_array($row) ? $row : null;
    }

    /**
     * @param string $query
     * @param array  $params
     * @return mixed
     */
    public function fetchSingleColumn(string $query, array $params = [])
    {
        $result = $this->executeQuery($query, $params);
        $row = $result->fetchArray(SQLITE3_NUM);
        return is_array($row) && isset($row[0]) ? $row[0] : null;
    }

    /**
     * @param string $query
     * @param array  $params
     * @return SQLite3Result
     */
    private function executeQuery(string $query, array $params)
    {
        $statement = $this->sqlite->prepare($query);
        if (!empty($params)) {
            $this->bindParameters($statement, $params);
        }
        if ($this->logger !== null) {
            $start = microtime(true);
            $this->logger->info('Executing query ' . $query);
            if (count($params) > 0) {
                $this->logger->info('With Parameters ' . print_r($params, true));
            }
        }
        $result = $statement->execute();
        if ($this->logger !== null) {
            $elapsed = microtime(true) - $start;
            $this->logger->info(sprintf('Finished query after %.3f seconds', $elapsed));
        }
        return $result;
    }

    /**
     * @param SQLite3Stmt $statement
     * @param array       $params
     */
    private function bindParameters(SQLite3Stmt $statement, array $params)
    {
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $key = ':' . $key;
            }
            if (is_object($value) && $value instanceof DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }
            $statement->bindValue($key, $value, $this->getParamType($value));
        }
    }

    /**
     * Returns one of the SQLITE3_* parameter type constants
     *
     * @param mixed $value
     * @return int
     */
    private function getParamType($value)
    {
        $valueType = strtolower(gettype($value));
        $paramTypeMap = [
            'integer' => SQLITE3_INTEGER,
            'string' => SQLITE3_TEXT,
            'double' => SQLITE3_FLOAT,
            'float' => SQLITE3_FLOAT,
            'null' => SQLITE3_NULL
        ];
        return isset($paramTypeMap[$valueType]) ? $paramTypeMap[$valueType] : SQLITE3_TEXT;
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