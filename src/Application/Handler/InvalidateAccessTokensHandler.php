<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\InvalidateAccessTokensCommand;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;

class InvalidateAccessTokensHandler
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
     */
    public function __invoke(InvalidateAccessTokensCommand $command): void
    {
        $user = $command->getAuthenticatedUser();
        $user->invalidateAccessTokens();

        $this->userRepository->save($user);
    }
}
