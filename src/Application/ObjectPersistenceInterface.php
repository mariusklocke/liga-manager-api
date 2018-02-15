<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use HexagonalPlayground\Application\Exception\NotFoundException;

interface ObjectPersistenceInterface
{
    /**
     * Finds an entity by its identifier
     *
     * @param string $class
     * @param mixed  $id
     * @return object
     * @throws NotFoundException
     */
    public function find(string $class, $id);

    /**
     * Schedule creation/update for an entity to persistence
     *
     * @param object $entity
     * @return mixed
     */
    public function persist($entity);

    /**
     * Schedule removal of an entity from persistence
     *
     * @param object $entity
     * @return mixed
     */
    public function remove($entity);

    /**
     * Wraps the execution of a callable into a transaction
     * 
     * All changes to entities will be made persistent once this method returns
     *
     * @param callable $callable
     * @return mixed
     */
    public function transactional(callable $callable);
}