<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\UpdateUserCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;

class UpdateUserHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(UserRepositoryInterface $userRepository, TeamRepositoryInterface $teamRepository)
    {
        $this->userRepository = $userRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param UpdateUserCommand $command
     */
    public function __invoke(UpdateUserCommand $command)
    {
        $user = $this->userRepository->findById($command->getUserId());

        // Changing other users than oneself requires admin rights
        if (!$user->equals($command->getAuthenticatedUser())) {
            IsAdmin::check($command->getAuthenticatedUser());
        }

        // Changing user role requires admin rights
        if (null !== $command->getRole() && !$user->hasRole($command->getRole())) {
            IsAdmin::check($command->getAuthenticatedUser());
            $user->setRole($command->getRole());
        }

        if (null !== $command->getEmail() && $command->getEmail() !== $user->getEmail()) {
            $this->userRepository->assertEmailDoesNotExist($command->getEmail());
            $user->setEmail($command->getEmail());
        }

        if (null !== $command->getFirstName()) {
            $user->setFirstName($command->getFirstName());
        }

        if (null !== $command->getLastName()) {
            $user->setLastName($command->getLastName());
        }

        if (null !== $command->getTeamIds()) {
            IsAdmin::check($command->getAuthenticatedUser());

            $user->clearTeams();
            foreach ($command->getTeamIds() as $teamId) {
                $user->addTeam($this->teamRepository->find($teamId));
            }
        }

        $this->userRepository->save($user);
    }
}