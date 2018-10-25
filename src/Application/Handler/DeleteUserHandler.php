<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteUserCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;

class DeleteUserHandler
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
     */
    public function __invoke(DeleteUserCommand $command)
    {
        IsAdmin::check($command->getAuthenticatedUser());
        $user = $this->userRepository->findById($command->getUserId());
        $this->userRepository->delete($user);
    }
}