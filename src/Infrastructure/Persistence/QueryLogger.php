<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use Doctrine\DBAL\Logging\SQLLogger;
use HexagonalPlayground\Infrastructure\Timer;
use Psr\Log\LoggerInterface;

class QueryLogger implements SQLLogger
{
    /** @var LoggerInterface */
    private $logger;

    /** @var Timer */
    private $timer;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->timer = new Timer();
    }

    /**
     * Starts a timer and logs query
     *
     * @param string $sql The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->timer->start();
        $this->logger->debug('Executing SQL query', [
            'sql' => $sql,
            'types' => $types
        ]);
    }

    /**
     * Logs the query execute time
     *
     * @return void
     */
    public function stopQuery()
    {
        $this->logger->debug('Finished query after {time} ms', ['time' => $this->timer->stop()]);
    }
}
