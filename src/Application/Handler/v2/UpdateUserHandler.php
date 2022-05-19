<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\UpdateUserCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Permission\IsAdmin;
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
        $user = $this->userRepository->find($command->getId());
        $isAdmin = new IsAdmin($authContext->getUser());

        // Changing other users than oneself requires admin rights
        if (!$user->equals($authContext->getUser())) {
            $isAdmin->check();
        }

        // Changing user role requires admin rights
        if ($command->getRole() !== $user->getRole()) {
            $isAdmin->check();
            $user->setRole($command->getRole());
        }

        // Changing email address requires new email address to be unused
        if ($command->getEmail() !== $user->getEmail()) {
            $this->userRepository->assertEmailDoesNotExist($command->getEmail());
            $user->setEmail($command->getEmail());
        }

        $user->setFirstName($command->getFirstName());
        $user->setLastName($command->getLastName());

        if (!$this->areArraysEqual($command->getTeamIds(), $user->getTeamIds())) {
            $isAdmin->check();

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

    private function areArraysEqual(array $array1, array $array2): bool
    {
        if (count(array_diff($array1, $array2)) > 0) {
            return false;
        }

        if (count(array_diff($array2, $array1)) > 0) {
            return false;
        }

        return true;
    }
}