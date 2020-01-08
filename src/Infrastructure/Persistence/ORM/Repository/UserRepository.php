<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Repository;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Exception\UniquenessException;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    /**
     * @param string $email
     * @return User
     * @throws NotFoundException
     */
    public function findByEmail(string $email): User
    {
        /** @var User|null $user */
        $user = $this->findOneBy(['email' => $email]);

        if (null === $user) {
            throw new NotFoundException('Cannot find user with email "' . $email . '"');
        }

        return $user;
    }

    /**
     * @param string $email
     * @throws UniquenessException
     */
    public function assertEmailDoesNotExist(string $email): void
    {
        try {
            $this->findByEmail($email);
        } catch (NotFoundException $e) {
            return;
        }

        throw new UniquenessException(
            sprintf("A user with email address %s already exists", $email)
        );
    }

    /**
     * @inheritDoc
     */
    protected static function getEntityClass(): string
    {
        return User::class;
    }
}