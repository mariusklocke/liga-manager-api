<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use HexagonalPlayground\Application\Security\AuthenticationException;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Application\Security\AuthContext;

class PasswordAuthenticator extends Authenticator
{
    /**
     * Authenticates a user using credentials
     *
     * @param string $email
     * @param string $password
     * @return AuthContext
     * @throws AuthenticationException
     */
    public function authenticate(string $email, string $password): AuthContext
    {
        try {
            $user = $this->userRepository->findByEmail($email);
        } catch (NotFoundException $e) {
            throw $this->createException();
        }

        if (false === $user->verifyPassword($password)) {
            throw $this->createException();
        }

        return new AuthContext($user);
    }
}
