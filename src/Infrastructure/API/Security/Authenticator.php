<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DateTimeImmutable;
use HexagonalPlayground\Application\Exception\AuthenticationException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Application\Security\TokenInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Domain\User;

class Authenticator
{
    /** @var TokenFactoryInterface */
    private $tokenFactory;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /**
     * @param TokenFactoryInterface $tokenFactory
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(TokenFactoryInterface $tokenFactory, UserRepositoryInterface $userRepository)
    {
        $this->tokenFactory       = $tokenFactory;
        $this->userRepository     = $userRepository;
    }

    /**
     * Authenticates a user using credentials
     *
     * @param string $email
     * @param string $password
     * @return User
     * @throws AuthenticationException
     */
    public function authenticateByCredentials(string $email, string $password): User
    {
        try {
            $user = $this->userRepository->findByEmail($email);
        } catch (NotFoundException $e) {
            throw $this->createException();
        }

        if (false === $user->verifyPassword($password)) {
            throw $this->createException();
        }

        return $user;
    }

    /**
     * Authenticates a user using an existing token
     *
     * The token will be considered invalid if the user password has been changed after the token has been issued
     *
     * @param TokenInterface $token
     * @return User
     * @throws AuthenticationException
     */
    public function authenticateByToken(TokenInterface $token): User
    {
        try {
            $user = $this->userRepository->findById($token->getUserId());
        } catch (NotFoundException $e) {
            throw $this->createException();
        }

        $now = new DateTimeImmutable();
        if ($token->getExpiresAt() < $now) {
            throw $this->createException('Token has expired');
        }

        if ($user->hasPasswordChangedSince($token->getIssuedAt())) {
            throw $this->createException('Password has changed after token has been issued.');
        }

        if ($user->haveAccessTokensBeenInvalidatedSince($token->getIssuedAt())) {
            throw $this->createException('Token has been invalidated');
        }

        return $user;
    }

    /**
     * @param string $message
     * @return AuthenticationException
     */
    private function createException(string $message = ''): AuthenticationException
    {
        return new AuthenticationException($message ?: 'Invalid Authentication');
    }
}