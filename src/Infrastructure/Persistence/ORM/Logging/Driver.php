<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Logging;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Psr\Log\LoggerInterface;

class Driver extends AbstractDriverMiddleware
{
    private LoggerInterface $logger;

    public function __construct(DriverInterface $driver, LoggerInterface $logger)
    {
        parent::__construct($driver);
        $this->logger = $logger;
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
