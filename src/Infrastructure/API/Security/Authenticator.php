<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use HexagonalPlayground\Application\Exception\AuthenticationException;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;

abstract class Authenticator
{
    /** @var UserRepositoryInterface */
    protected $userRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param string $message
     * @return AuthenticationException
     */
    protected function createException(string $message = ''): AuthenticationException
    {
        return new AuthenticationException($message ?: 'Invalid Authentication');
    }
}