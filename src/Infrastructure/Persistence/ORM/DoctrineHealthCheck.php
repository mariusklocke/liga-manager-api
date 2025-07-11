<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\EntityManagerClosed;
use Exception;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Psr\Container\ContainerInterface;

class DoctrineHealthCheck implements HealthCheckInterface
{
    /** @var ContainerInterface */
    private ContainerInterface $container;

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
        /** @var Connection */
        $connection = $this->container->get(Connection::class);
        $connection->executeQuery('SELECT 1');

        /** @var EntityManagerInterface */
        $entityManager = $this->container->get(EntityManagerInterface::class);
        if (!$entityManager->isOpen()) {
            throw EntityManagerClosed::create();
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'doctrine';
    }
}
