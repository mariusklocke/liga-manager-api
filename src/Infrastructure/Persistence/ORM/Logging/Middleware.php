<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Logging;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;
use Psr\Log\LoggerInterface;

class Middleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new Driver($driver, $this->logger);
    }
}
