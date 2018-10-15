<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteUserCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Exception\PermissionException;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Domain\User;

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
     * @throws PermissionException
     * @throws NotFoundException
     */
    public function __invoke(DeleteUserCommand $command)
    {
        $this->checkPermissions($command);
        $user = $this->userRepository->findById($command->getUserId());
        $this->userRepository->delete($user);
    }

    /**
     * @param DeleteUserCommand $command
     * @throws PermissionException
     */
    private function checkPermissions(DeleteUserCommand $command)
    {
        if ($command->getAuthenticatedUser()->hasRole(User::ROLE_ADMIN)) {
            return;
        }

        throw new PermissionException('Authenticated user is not allowed to delete users');
    }
}