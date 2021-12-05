<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\QueryLogger;
use mysqli;
use mysqli_driver;
use mysqli_result;
use mysqli_stmt;

class MysqliReadDbAdapter implements ReadDbAdapterInterface
{
    /** @var mysqli */
    private $mysqli;

    /** @var QueryLogger */
    private $logger;

    /**
     * @param mysqli $mysqli
     */
    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
        $driver = new mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_ALL & ~MYSQLI_REPORT_INDEX;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll(string $query, array $params = []) : array
    {
        $result = $this->executeQuery($query, $params);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchFirstRow(string $query, array $params): ?array
    {
        $result = $this->executeQuery($query, $params);
        return $result->fetch_assoc();
    }

    /**
     * @param QueryLogger $logger
     * @return MysqliReadDbAdapter
     */
    public function setLogger(QueryLogger $logger) : self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Executes a prepared statement and returns the result set
     *
     * @param string $query
     * @param array $params
     * @return mysqli_result
     */
    private function executeQuery(string $query, array $params) : mysqli_result
    {
        $this->logger->startQuery($query, $params, []);
        $statement = $this->mysqli->prepare($query);
        $this->bindParams($statement, $params);
        $statement->execute();
        $result = $statement->get_result();
        $this->logger->stopQuery();
        return $result;
    }

    /**
     * Determines the mysqli-specific parameter type
     *
     * @param mixed $value
     * @return string
     */
    private function getParamType($value) : string
    {
        $valueType = strtolower(gettype($value));
        $paramTypeMap = [
            'integer' => 'i',
            'string' => 's',
            'double' => 'd'
        ];
        return isset($paramTypeMap[$valueType]) ? $paramTypeMap[$valueType] : 's';
    }

    /**
     * Bind parameters to a prepared statement
     *
     * @param mysqli_stmt $statement
     * @param array $params
     */
    private function bindParams(mysqli_stmt $statement, array $params)
    {
        $refs = [];
        $refs[0] = '';
        foreach ($params as $key => $value) {
            $refs[0] .= $this->getParamType($value);
            $refs[] = &$params[$key];
        }
        if (strlen($refs[0]) > 0) {
            call_user_func_array([$statement, 'bind_param'], $refs);
        }
    }
}
