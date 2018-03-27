<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Domain\User;

interface UserRepositoryInterface
{
    /**
     * Finds a user by Id
     *
     * @param string $id
     * @return User
     * @throws NotFoundException if user does not exist
     */
    public function findById(string $id): User;

    /**
     * Finds a user by email address
     *
     * @param string $email
     * @return User
     * @throws NotFoundException if user does not exists
     */
    public function findByEmail(string $email): User;
}