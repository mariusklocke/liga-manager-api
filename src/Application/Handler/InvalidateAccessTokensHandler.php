<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\InvalidateAccessTokensCommand;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Domain\Event\Event;

class InvalidateAccessTokensHandler implements AuthAwareHandler
{
    /** @var UserRepositoryInterface */
    private UserRepositoryInterface $userRepository;

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
     * @return array|Event[]
     */
    public function __invoke(InvalidateAccessTokensCommand $command, AuthContext $authContext): array
    {
        $user = $authContext->getUser();
        $user->invalidateAccessTokens();

        $this->userRepository->save($user);

        return [];
    }
}
