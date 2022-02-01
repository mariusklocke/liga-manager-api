<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\DBAL\Connection;
use Exception;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Psr\Container\ContainerInterface;

class DoctrineHealthCheck implements HealthCheckInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws Exception
     */
    public function __invoke(): void
    {
        $this->container->get(Connection::class)->executeQuery('SELECT 1');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'doctrine';
    }
}
