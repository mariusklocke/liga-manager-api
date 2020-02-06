<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use Doctrine\DBAL\Logging\SQLLogger;
use Psr\Log\LoggerInterface;

class QueryLogger implements SQLLogger
{
    /** @var LoggerInterface */
    private $logger;

    /** @var float|null */
    private $startTime;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string $sql The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->startTime = microtime(true);
        $this->logger->info('Executing SQL query', [
            'sql' => $sql,
            'types' => $types
        ]);
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        if ($this->startTime !== null) {
            $time = microtime(true) - $this->startTime;
            $this->logger->info(sprintf('Finished query after %.3f ms', $time * 1000));
            $this->startTime = null;
        }
    }
}
