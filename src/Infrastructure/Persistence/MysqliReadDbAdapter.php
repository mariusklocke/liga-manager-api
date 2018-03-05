<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Application\ReadDbAdapterInterface;
use mysqli;
use mysqli_result;

class MysqliReadDbAdapter implements ReadDbAdapterInterface
{
    /** @var mysqli */
    private $mysqli;

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
     * Executes a prepared statement and returns the result set
     *
     * @param string $query
     * @param array $params
     * @return mysqli_result
     */
    private function executeQuery(string $query, array $params) : mysqli_result
    {
        $statement = $this->mysqli->prepare($query);
        $refs = [];
        $refs[0] = '';
        foreach ($params as $key => $value) {
            $refs[0] .= $this->getParamType($value);
            $refs[] = &$params[$key];
        }
        call_user_func_array([$statement, 'bind_param'], $refs);
        $statement->execute();
        return $statement->get_result();
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
}