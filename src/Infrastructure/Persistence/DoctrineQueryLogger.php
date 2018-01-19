<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use Doctrine\DBAL\Logging\SQLLogger;
use Psr\Log\LoggerInterface;

class DoctrineQueryLogger implements SQLLogger
{
    /** @var LoggerInterface */
    private $logger;
    /** @var float */
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
        $this->logger->info('Executing query ' . $sql);
        if (count($params) > 0) {
            $this->logger->info('With parameter values ' . var_export($params, true));
            $this->logger->info('With parameter types ' . var_export($types, true));
        }
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        if ($this->startTime > 0) {
            $time = microtime(true) - $this->startTime;
            $this->logger->info(sprintf('Finished query after %.3f seconds', $time));
        }
        $this->startTime = null;
    }
}
