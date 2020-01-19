<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;

class DoctrineHealthCheck implements HealthCheckInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @throws Exception
     */
    public function __invoke(): void
    {
        $this->em->getConnection()->executeQuery('SELECT 1');
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Database connection via Doctrine';
    }
}