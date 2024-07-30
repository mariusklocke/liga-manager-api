<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Logging;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class Driver extends AbstractDriverMiddleware
{
    private LoggerInterface $logger;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(DriverInterface $driver, LoggerInterface $logger, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($driver);
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(array $params): Connection
    {
        $this->logger->debug('Connecting to database', ['params' => $this->maskPassword($params)]);

        return new Connection(
            parent::connect($params),
            $this->logger,
            $this->eventDispatcher
        );
    }

    /**
     * @param array<string,mixed> $params Connection parameters
     *
     * @return array<string,mixed>
     */
    private function maskPassword(array $params): array {
        if (isset($params['password'])) {
            $params['password'] = '<redacted>';
        }

        return $params;
    }
}
