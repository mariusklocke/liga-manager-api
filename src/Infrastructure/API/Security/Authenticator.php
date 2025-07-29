<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use HexagonalPlayground\Application\Security\AuthenticationException;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;

abstract class Authenticator
{
    /** @var UserRepositoryInterface */
    protected UserRepositoryInterface $userRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param string $messageId
     * @return AuthenticationException
     */
    protected function createException(string $messageId = ''): AuthenticationException
    {
        return new AuthenticationException($messageId ?: 'invalidAuthentication');
    }
}
