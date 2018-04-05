<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\ObjectPersistenceInterface;

class DoctrineObjectPersistence implements ObjectPersistenceInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $class
     * @param mixed $id
     * @return object
     * @throws DoctrineException
     * @throws NotFoundException
     */
    public function find(string $class, $id)
    {
        $object = $this->wrapDoctrineException(function () use ($class, $id) {
            return $this->entityManager->find($class, $id);
        });

        if (!is_object($object)) {
            throw new NotFoundException(
                sprintf('Cannot find %s with ID %s', $this->stripNamespace($class), $id)
            );
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
        $this->wrapDoctrineException(function () use ($entity) {
            $this->entityManager->persist($entity);
        });
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
        $this->wrapDoctrineException(function () use ($entity) {
            $this->entityManager->remove($entity);
        });
    }

    /**
     * Wraps the execution of a callable into a transaction
     *
     * All changes to entities will be made persistent once this method returns
     *
     * @param callable $callable
     * @return mixed
     */
    public function transactional(callable $callable)
    {
        return $this->wrapDoctrineException(function () use ($callable) {
            return $this->entityManager->transactional($callable);
        });
    }

    /**
     * @param callable $callable
     * @return mixed
     */
    private function wrapDoctrineException(callable $callable)
    {
        try {
            return call_user_func($callable);
        } catch (ORMException $e) {
            throw new DoctrineException("EntityManager threw unexpected Exception. See previous exception", 0, $e);
        } catch (DBALException $e) {
            throw new DoctrineException("EntityManager threw unexpected Exception. See previous exception", 0, $e);
        }
    }

    /**
     * Strips the namespace part from a fully qualified class name
     *
     * @param string $className fully qualified class name
     * @return string
     */
    private function stripNamespace(string $className) : string
    {
        return substr($className, strrpos($className, '\\') + 1);
    }
}
