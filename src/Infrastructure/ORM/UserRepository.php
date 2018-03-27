<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\ORM;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * @param string $id
     * @return User
     * @throws NotFoundException
     */
    public function findById(string $id): User
    {
        /** @var User $user */
        $user = $this->find($id);
        if (null === $user) {
            throw new NotFoundException('Cannot find User with Id ' . $id);
        }

        return $user;
    }

    /**
     * @param string $email
     * @return User
     * @throws NotFoundException
     */
    public function findByEmail(string $email): User
    {
        /** @var User $user */
        $user = $this->findOneBy(['email' => $email]);
        if (null === $user) {
            throw new NotFoundException('Cannot find User with email "' . $email . '"');
        }

        return $user;
    }
}