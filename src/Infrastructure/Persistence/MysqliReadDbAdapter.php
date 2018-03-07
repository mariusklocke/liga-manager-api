<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use Doctrine\DBAL\Logging\SQLLogger;
use HexagonalPlayground\Application\ReadDbAdapterInterface;
use mysqli;
use mysqli_result;
use mysqli_stmt;

class MysqliReadDbAdapter implements ReadDbAdapterInterface
{
    /** @var mysqli */
    private $mysqli;

    /** @var SQLLogger */
    private $logger;

    /**
     * @param mysqli $mysqli
     */
    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
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
    public function fetchFirstRow(string $query, array $params = [])
    {
        $result = $this->executeQuery($query, $params);
        $row = $result->fetch_assoc();
        return is_array($row) ? $row : null;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchSingleColumn(string $query, array $params = [])
    {
        $result = $this->executeQuery($query, $params);
        $row = $result->fetch_row();
        return is_array($row) && isset($row[0]) ? $row[0] : null;
    }

    /**
     * @param SQLLogger $logger
     * @return MysqliReadDbAdapter
     */
    public function setLogger(SQLLogger $logger) : self
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