<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Repository;

use HexagonalPlayground\Application\Exception\NotFoundException;

interface EntityRepositoryInterface
{
    /**
     * @return array|object[]
     */
    public function findAll(): array;

    /**
     * @param string $id
     * @return object
     * @throws NotFoundException
     */
    public function find(string $id): object;

    /**
     * @param string $id
     * @return object|null
     */
    public function get(string $id): ?object;

    /**
     * @param object $entity
     */
    public function save($entity): void;

    /**
     * @param object $entity
     */
    public function delete($entity): void;
}
