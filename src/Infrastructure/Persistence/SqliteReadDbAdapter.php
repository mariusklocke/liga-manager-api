<?php
/**
 * SqliteReadDbAdapter.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Application\ReadDbAdapterInterface;
use SQLite3;
use SQLite3Result;
use SQLite3Stmt;

class SqliteReadDbAdapter implements ReadDbAdapterInterface
{
    /** @var SQLite3 */
    private $sqlite;

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
        $result = $statement->execute();
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
            'integer' => SQLITE3_INTEGER,
            'string' => SQLITE3_TEXT,
            'null' => SQLITE3_NULL
        ];
        return isset($paramTypeMap[$valueType]) ? $paramTypeMap[$valueType] : SQLITE3_TEXT;
    }
}