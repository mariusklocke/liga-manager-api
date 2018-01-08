<?php

namespace HexagonalPlayground\Application;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Exception\PersistenceExceptionInterface;

interface ObjectPersistenceInterface
{
    /**
     * Finds an entity by its identifier
     *
     * @param string $class
     * @param mixed  $id
     * @param bool   $lock
     * @return object
     * @throws PersistenceExceptionInterface
     * @throws NotFoundException
     */
    public function find(string $class, $id, $lock = false);

    /**
     * Schedule creation/update for an entity to persistence
     *
     * @param object $entity
     * @return mixed
     * @throws PersistenceExceptionInterface
     */
    public function persist($entity);

    /**
     * Schedule removal of an entity from persistence
     *
     * @param object $entity
     * @return mixed
     * @throws PersistenceExceptionInterface
     */
    public function remove($entity);

    /**
     * Wraps the execution of a callable into a transaction
     * 
     * All changes to entities will be made persistent once this method returns
     *
     * @param callable $callable
     * @return mixed
     * @throws PersistenceExceptionInterface
     */
    public function transactional(callable $callable);
}