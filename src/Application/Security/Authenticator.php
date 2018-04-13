<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use DateTimeImmutable;
use HexagonalPlayground\Application\Exception\AuthenticationException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Domain\User;

class Authenticator
{
    /** @var User|null */
    private $authenticatedUser;

    /** @var TokenInterface|null */
    private $authenticatedToken;

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
        $this->authenticatedUser  = null;
        $this->authenticatedToken = null;
    }

    /**
     * Authenticates a user using credentials
     *
     * @param string $email
     * @param string $password
     * @throws AuthenticationException
     */
    public function authenticateByCredentials(string $email, string $password): void
    {
        try {
            $user = $this->userRepository->findByEmail($email);
        } catch (NotFoundException $e) {
            throw $this->createException();
        }

        if (false === $user->verifyPassword($password)) {
            throw $this->createException();
        }

        $this->authenticatedUser = $user;
    }

    /**
     * Authenticates a user using an existing token
     *
     * The token will be considered invalid if the user password has been changed after the token has been issued
     *
     * @param TokenInterface $token
     * @throws AuthenticationException
     */
    public function authenticateByToken(TokenInterface $token): void
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

        $this->authenticatedUser = $user;
    }

    /**
     * @return User
     * @throws AuthenticationException
     */
    public function getAuthenticatedUser(): User
    {
        if (null === $this->authenticatedUser) {
            throw $this->createException();
        }
        return $this->authenticatedUser;
    }

    /**
     * @return TokenInterface
     */
    public function getAuthenticatedToken(): TokenInterface
    {
        if (null === $this->authenticatedToken) {
            $this->authenticatedToken = $this->tokenFactory->create(
                $this->getAuthenticatedUser(),
                new DateTimeImmutable('now + 1 year')
            );
        }
        return $this->authenticatedToken;
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