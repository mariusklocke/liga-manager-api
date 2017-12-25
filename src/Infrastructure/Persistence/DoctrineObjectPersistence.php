<?php

namespace HexagonalDream\Infrastructure\Persistence;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use HexagonalDream\Application\ObjectPersistenceInterface;

class DoctrineObjectPersistence implements ObjectPersistenceInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $class
     * @param mixed  $id
     * @param bool $lock
     * @return null|object
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function find(string $class, $id, $lock = false)
    {
        $lockMode = $lock ? LockMode::PESSIMISTIC_WRITE : LockMode::NONE;
        return $this->entityManager->find($class, $id, $lockMode);
    }

    /**
     * Schedule creation/update for an entity to persistence
     *
     * @param object $entity
     * @return void
     */
    public function persist($entity)
    {
        $this->entityManager->persist($entity);
    }

    /**
     * Schedule removal of an entity from persistence
     *
     * @param object $entity
     * @return void
     */
    public function remove($entity)
    {
        $this->entityManager->remove($entity);
    }

    /**
     * Wraps the execution of a callable into a transaction
     *
     * All changes to entities will be made persistent once this method returns
     *
     * @param callable $callable
     * @return mixed
     * @throws \Exception
     */
    public function transactional(callable $callable)
    {
        return $this->entityManager->transactional($callable);
    }
}
