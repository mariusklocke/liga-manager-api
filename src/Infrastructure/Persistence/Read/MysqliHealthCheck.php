<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use Exception;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use mysqli;
use RuntimeException;

class MysqliHealthCheck implements HealthCheckInterface
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
     * @throws Exception
     */
    public function __invoke(): void
    {
        if ($this->mysqli->connect_error) {
            throw new RuntimeException('Could not connect to database: ' . $this->mysqli->connect_error);
        }
        $this->mysqli->query('SELECT 1');
        if ($this->mysqli->error) {
            throw new RuntimeException('Could not query database: ' . $this->mysqli->error);
        }
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Database connection via mysqli';
    }
}