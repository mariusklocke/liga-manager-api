<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\InvalidateAccessTokensCommand;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;

class InvalidateAccessTokensHandler implements AuthAwareHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param InvalidateAccessTokensCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(InvalidateAccessTokensCommand $command, AuthContext $authContext): void
    {
        $user = $authContext->getUser();
        $user->invalidateAccessTokens();

        $this->userRepository->save($user);
    }
}
