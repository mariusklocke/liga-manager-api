<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\ORM\EntityManagerInterface;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;

class DoctrineTransactionWrapper implements OrmTransactionWrapperInterface
{
    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function transactional(callable $callable)
    {
        $result = call_user_func($callable);

        $this->entityManager->flush();

        return $result;
    }
}
