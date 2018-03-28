<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Factory;

use HexagonalPlayground\Application\IdGeneratorInterface;
use HexagonalPlayground\Domain\User;

class UserFactory extends EntityFactory
{
    /**
     * @param string $email
     * @param string $password
     * @return User
     */
    public function createUser(string $email, string $password)
    {
        return new User(
            $this->getIdGenerator()->generate(),
            $email,
            $password
        );
    }
}