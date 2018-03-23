<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use HexagonalPlayground\Application\Exception\NotFoundException;

class UserRepository
{
    /** @var User */
    private static $dummyUser;

    public function findByEmail(string $email): User
    {
        if ($this->getDummyUser()->getEmail() === $email) {
            return $this->getDummyUser();
        }
        throw new NotFoundException('User not found');
    }

    public function findById(string $id): User
    {
        if ($this->getDummyUser()->getId() === $id) {
            return $this->getDummyUser();
        }
        throw new NotFoundException('User not found');
    }

    private function getDummyUser()
    {
        if (null === self::$dummyUser) {
            self::$dummyUser = new User('123', 'admin', 'admin');
        }
        return self::$dummyUser;
    }
}