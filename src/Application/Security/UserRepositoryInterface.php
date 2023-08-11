<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Domain\Exception\UniquenessException;
use HexagonalPlayground\Application\Repository\EntityRepositoryInterface;
use HexagonalPlayground\Domain\User;

interface UserRepositoryInterface extends EntityRepositoryInterface
{
    /**
     * Finds a user by email address
     *
     * @param string $email
     * @return User
     * @throws NotFoundException if user does not exists
     */
    public function findByEmail(string $email): User;

    /**
     * @param string $email
     * @throws UniquenessException
     */
    public function assertEmailDoesNotExist(string $email): void;
}
