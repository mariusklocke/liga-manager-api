<?php

namespace HexagonalDream\Infrastructure\Persistence;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Exception;
use HexagonalDream\Application\Exception\NotFoundException;
use HexagonalDream\Application\ObjectPersistenceInterface;
use Throwable;

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
     * @param mixed $id
     * @param bool $lock
     * @return object
     * @throws DoctrineException
     * @throws NotFoundException
     */
    public function find(string $class, $id, $lock = false)
    {
        try {
            $object = $this->entityManager->find($class, $id);
        } catch (ORMException $e) {
            throw $this->wrapDoctrineException($e);
        } catch (DBALException $e) {
            throw $this->wrapDoctrineException($e);
        }

        if (!is_object($object)) {
            throw new NotFoundException(sprintf('Cannot find entity "%s" with ID "%s"', $class, $id));
        }

        return $object;
    }

    /**
     * Schedule creation/update for an entity to persistence
     *
     * @param object $entity
     * @return void
     * @throws DoctrineException
     */
    public function persist($entity)
    {
        try {
            $this->entityManager->persist($entity);
        } catch (ORMException $e) {
            throw $this->wrapDoctrineException($e);
        } catch (DBALException $e) {
            throw $this->wrapDoctrineException($e);
        }
    }

    /**
     * Schedule removal of an entity from persistence
     *
     * @param object $entity
     * @return void
     * @throws DoctrineException
     */
    public function remove($entity)
    {
        try {
            $this->entityManager->remove($entity);
        } catch (ORMException $e) {
            throw $this->wrapDoctrineException($e);
        } catch (DBALException $e) {
            throw $this->wrapDoctrineException($e);
        }
    }

    /**
     * Wraps the execution of a callable into a transaction
     *
     * All changes to entities will be made persistent once this method returns
     *
     * @param callable $callable
     * @return mixed
     * @throws Throwable
     * @throws DoctrineException
     */
    public function transactional(callable $callable)
    {
        try {
            return $this->entityManager->transactional($callable);
        } catch (ORMException $e) {
            throw $this->wrapDoctrineException($e);
        } catch (DBALException $e) {
            throw $this->wrapDoctrineException($e);
        }
    }

    /**
     * @param Exception $e
     * @return DoctrineException
     */
    private function wrapDoctrineException(Exception $e)
    {
        return new DoctrineException("EntityManager threw unexpected Exception. See previous exception", 0, $e);
    }
}
