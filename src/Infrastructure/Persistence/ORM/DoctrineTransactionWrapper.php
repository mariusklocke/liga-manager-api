<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\ORM\EntityManagerInterface;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use Throwable;

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
        $this->entityManager->beginTransaction();

        try {
            $return = call_user_func($callable);

            $this->entityManager->flush();
            $this->entityManager->commit();

            return $return;
        } catch (Throwable $e) {
            $this->entityManager->clear();
            $this->entityManager->rollback();

            throw $e;
        }
    }
}