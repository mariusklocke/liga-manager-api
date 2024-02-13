<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Logging;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as DriverStatement;
use Psr\Log\LoggerInterface;

class Connection extends AbstractConnectionMiddleware
{
    private LoggerInterface $logger;

    public function __construct(ConnectionInterface $connection, LoggerInterface $logger)
    {
        parent::__construct($connection);
        $this->logger = $logger;
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
        );
    }

    public function query(string $sql): Result
    {
        $this->logger->debug('Executing database query', ['sql' => $sql]);

        return parent::query($sql);
    }

    public function exec(string $sql): int|string
    {
        $this->logger->debug('Executing database statement', ['sql' => $sql]);

        return parent::exec($sql);
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
    }

    public function rollBack(): void
    {
        $this->logger->debug('Rolling back database transaction');

        parent::rollBack();
    }
}
