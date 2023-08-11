<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\UpdateUserCommand;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class UpdateUserHandler implements AuthAwareHandler
{
    /** @var UserRepositoryInterface */
    private UserRepositoryInterface $userRepository;

    /** @var TeamRepositoryInterface */
    private TeamRepositoryInterface $teamRepository;

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
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(UpdateUserCommand $command, AuthContext $authContext): array
    {
        /** @var User $user */
        $user = $this->userRepository->find($command->getUserId());

        // Changing other users than oneself requires admin rights
        if (!$user->equals($authContext->getUser())) {
            $authContext->getUser()->assertIsAdmin();
        }

        // Changing user role requires admin rights
        if (null !== $command->getRole() && !$user->hasRole($command->getRole())) {
            $authContext->getUser()->assertIsAdmin();
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
            $authContext->getUser()->assertIsAdmin();

            $user->clearTeams();
            foreach ($command->getTeamIds() as $teamId) {
                /** @var Team $team */
                $team = $this->teamRepository->find($teamId);
                $user->addTeam($team);
            }
        }

        $this->userRepository->save($user);

        return [];
    }
}
