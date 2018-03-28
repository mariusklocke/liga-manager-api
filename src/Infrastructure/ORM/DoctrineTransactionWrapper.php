<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\ORM;

use Doctrine\ORM\EntityManagerInterface;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;

class DoctrineTransactionWrapper implements OrmTransactionWrapperInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function transactional(callable $callable)
    {
        return $this->entityManager->transactional($callable);
    }
}