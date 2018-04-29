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
     * @param string $firstName
     * @param string $lastName
     * @return User
     */
    public function createUser(string $email, string $password, string $firstName, string $lastName)
    {
        return new User($email, $password, $firstName, $lastName);
    }
}