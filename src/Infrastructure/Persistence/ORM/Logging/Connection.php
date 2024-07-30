<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Logging;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as DriverStatement;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Event\QueryEvent;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Event\TransactionCommitEvent;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Event\TransactionRollbackEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class Connection extends AbstractConnectionMiddleware
{
    private LoggerInterface $logger;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(ConnectionInterface $connection, LoggerInterface $logger, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($connection);
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __destruct()
    {
        $this->logger->debug('Disconnecting from database');
    }

    public function prepare(string $sql): DriverStatement
    {
        return new Statement(
            parent::prepare($sql),
            $this->logger,
            $sql,
            $this->eventDispatcher
        );
    }

    public function query(string $sql): Result
    {
        $this->logger->debug('Executing database query', ['sql' => $sql]);
        $result = parent::query($sql);
        $this->eventDispatcher->dispatch(new QueryEvent($sql));

        return $result;
    }

    public function exec(string $sql): int|string
    {
        $this->logger->debug('Executing database statement', ['sql' => $sql]);
        $result = parent::exec($sql);
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
}
