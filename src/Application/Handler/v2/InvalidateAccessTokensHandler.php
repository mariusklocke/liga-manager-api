<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\InvalidateAccessTokensCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;

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
        if ($command->getUserId() !== $authContext->getUser()->getId()) {
            // Invalidating other users access tokens requires admin role
            $authContext->getUser()->assertIsAdmin();

            $user = $this->userRepository->find($command->getUserId());
        } else {
            $user = $authContext->getUser();
        }

        $user->invalidateAccessTokens();

        $this->userRepository->save($user);

        return [];
    }
}
