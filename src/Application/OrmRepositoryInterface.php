<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use HexagonalPlayground\Application\Exception\NotFoundException;

interface OrmRepositoryInterface
{
    /**
     * Finds an entity by id
     *
     * @param mixed $id
     * @return object
     * @throws NotFoundException
     */
    public function find($id);

    /**
     * Schedule creation/update for an entity to persistence
     *
     * @param object $entity
     */
    public function save($entity): void;

    /**
     * Schedule removal of an entity from persistence
     *
     * @param $entity
     */
    public function delete($entity): void;
}