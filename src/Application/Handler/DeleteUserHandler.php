<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteUserCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\User;

class DeleteUserHandler implements AuthAwareHandler
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
     * @param DeleteUserCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(DeleteUserCommand $command, AuthContext $authContext): array
    {
        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        /** @var User $user */
        $user = $this->userRepository->find($command->getUserId());
        $this->userRepository->delete($user);

        return [];
    }
}
