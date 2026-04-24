<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use SensitiveParameter;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Exception\ConnectionLost;
use Doctrine\DBAL\Result;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Event\QueryEvent;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Event\TransactionCommitEvent;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Event\TransactionRollbackEvent;
use Psr\Log\LoggerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class Connection extends \Doctrine\DBAL\Connection
{
    private LoggerInterface $logger;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        #[SensitiveParameter]
        array $params,
        protected Driver $driver,
        ?Configuration $config = null,
    ) {
        parent::__construct($params, $driver, $config);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function connect(): DriverConnection
    {
        $delaySeconds = 5;
        $maxAttempts = 3;
        $attempt = 1;
        $result = null;
        while ($attempt <= $maxAttempts) {
            $this->logger->debug('Connecting to database', ['params' => $this->getMaskedParams()]);
            try {
                $result = parent::connect();
                break;
            } catch (Throwable $exception) {
                if ($attempt === $maxAttempts) {
                    throw $exception;
                }
                $this->logger->warning('Database connection failed, retrying...', ['attempt' => $attempt]);
                sleep($delaySeconds);
                $attempt++;
            }
        }
        return $result;
    }

    protected function disconnect(): void
    {
        $this->logger->debug('Disconnecting from database');
        $this->_conn = null;
    }

    public function executeQuery(string $sql, array $params = [], array $types = [], ?QueryCacheProfile $qcp = null): Result
    {
        $delaySeconds = 5;
        $maxAttempts = 3;
        $attempt = 1;
        $result = null;
        while ($attempt <= $maxAttempts) {
            $this->logger->debug('Executing database query', ['sql' => $sql]);
            try {
                $result = parent::executeQuery($sql, $params, $types, $qcp);
                break;
            } catch (ConnectionLost $exception) {
                if ($attempt === $maxAttempts) {
                    throw $exception;
                }
                $this->logger->warning('Database connection lost, retrying...', ['attempt' => $attempt]);
                $this->disconnect();
                sleep($delaySeconds);
                $attempt++;
            } catch (Throwable $e) {
                throw $e; // never retry other exceptions
            }
        }
        $this->eventDispatcher->dispatch(new QueryEvent($sql));
        return $result;
    }

    public function executeStatement(string $sql, array $params = [], array $types = []): int|string
    {
        $this->logger->debug('Executing database statement', ['sql' => $sql]);
        $result = parent::executeStatement($sql, $params, $types);
        $this->eventDispatcher->dispatch(new QueryEvent($sql));
        return $result;
    }

    public function beginTransaction(): void
    {
        $this->logger->debug('Beginning database transaction');
        parent::beginTransaction();
    }

    public function commit(): void
    {
        $this->logger->debug('Committing database transaction');
        parent::commit();
        $this->eventDispatcher->dispatch(new TransactionCommitEvent());
    }

    public function rollBack(): void
    {
        $this->logger->debug('Rolling back database transaction');
        parent::rollBack();
        $this->eventDispatcher->dispatch(new TransactionRollbackEvent());
    }

    /**
     * @return array<string,mixed>
     */
    private function getMaskedParams(): array
    {
        $params = $this->getParams();

        if (isset($params['password'])) {
            $params['password'] = '<redacted>';
        }

        return $params;
    }
}