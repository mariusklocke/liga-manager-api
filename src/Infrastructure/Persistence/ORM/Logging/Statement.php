<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Logging;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Event\QueryEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class Statement extends AbstractStatementMiddleware
{
    private LoggerInterface $logger;
    private string $sql;
    private array $params = [];
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        StatementInterface $statement,
        LoggerInterface $logger,
        string $sql,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($statement);
        $this->logger = $logger;
        $this->sql = $sql;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function bindValue(int|string $param, mixed $value, ParameterType $type): void
    {
        $this->params[$param] = $value;

        parent::bindValue($param, $value, $type);
    }

    public function execute(): ResultInterface
    {
        $this->logger->debug('Executing database statement', [
            'sql'    => $this->sql,
            'params' => $this->params
        ]);
        $result = parent::execute();
        $this->eventDispatcher->dispatch(new QueryEvent($this->sql, $this->params));

        return $result;
    }
}
