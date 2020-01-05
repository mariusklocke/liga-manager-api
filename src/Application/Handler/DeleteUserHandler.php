<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteUserCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;

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
     */
    public function __invoke(DeleteUserCommand $command, AuthContext $authContext)
    {
        IsAdmin::check($authContext->getUser());
        $user = $this->userRepository->findById($command->getUserId());
        $this->userRepository->delete($user);
    }
}