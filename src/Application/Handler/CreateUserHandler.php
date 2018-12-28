<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Permission\CanManageTeam;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class CreateUserHandler
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
     * @param CreateUserCommand $command
     * @return string
     */
    public function __invoke(CreateUserCommand $command)
    {
        if ($command->getRole() === User::ROLE_ADMIN) {
            IsAdmin::check($command->getAuthenticatedUser());
        }

        $this->userRepository->assertEmailDoesNotExist($command->getEmail());
        $user = new User($command->getEmail(), $command->getPassword(), $command->getFirstName(), $command->getLastName());
        $user->setRole($command->getRole());
        foreach ($command->getTeamIds() as $teamId) {
            /** @var Team $team */
            $team = $this->teamRepository->find($teamId);
            CanManageTeam::check($team, $command->getAuthenticatedUser());
            $user->addTeam($team);
        }
        $this->userRepository->save($user);
        return $user->getId();
    }
}