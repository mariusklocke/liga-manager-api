<?php

namespace HexagonalDream\Infrastructure\Persistence;

use Exception;
use HexagonalDream\Application\ObjectPersistenceInterface;
use SplObjectStorage;

class InMemoryObjectPersistence implements ObjectPersistenceInterface
{
    /** @var array */
    private $scheduledPersists;

    /** @var array */
    private $scheduledRemovals;

    /** @var SplObjectStorage */
    private $storage;

    public function __construct()
    {
        $this->scheduledPersists = [];
        $this->scheduledRemovals = [];
        $this->storage = new SplObjectStorage();
    }

    /**
     * Schedule creation/update for an entity to persistence
     *
     * @param object $entity
     */
    public function persist($entity)
    {
        $id = spl_object_hash($entity);
        $this->scheduledPersists[$id] = $entity;
    }

    /**
     * Schedule removal of an entity from persistence
     *
     * @param object $entity
     */
    public function remove($entity)
    {
        $id = spl_object_hash($entity);
        $this->scheduledRemovals[$id] = $entity;
    }

    /**
     * Wraps the execution of a callable into a transaction
     *
     * All changes to entities will be made persistent once this method returns
     *
     * @param callable $callable
     * @throws Exception
     */
    public function transactional(callable $callable)
    {
        try {
            $this->resetScheduled();
            call_user_func($callable);
            $this->writeToStorage();
            $this->resetScheduled();
        } catch (Exception $e) {
            $this->resetScheduled();
            throw $e;
        }
    }

    private function writeToStorage()
    {
        foreach ($this->scheduledPersists as $entity) {
            $this->storage->offsetSet($entity);
        }

        foreach ($this->scheduledRemovals as $entity) {
            $this->storage->offsetUnset($entity);
        }
    }

    private function resetScheduled()
    {
        $this->scheduledRemovals = [];
        $this->scheduledPersists = [];
    }

    /**
     * @param string $class
     * @param mixed $id
     * @param bool $lock
     * @return object|null
     */
    public function find(string $class, $id, $lock = false)
    {
        // TODO: Implement find() method.
    }
}
