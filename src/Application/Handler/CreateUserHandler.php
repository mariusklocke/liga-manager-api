<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class CreateUserHandler implements AuthAwareHandler
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
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(CreateUserCommand $command, AuthContext $authContext): array
    {
        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        $this->userRepository->assertEmailDoesNotExist($command->getEmail());
        $user = new User($command->getId(), $command->getEmail(), $command->getPassword(), $command->getFirstName(), $command->getLastName());
        $user->setRole($command->getRole());
        foreach ($command->getTeamIds() as $teamId) {
            /** @var Team $team */
            $team = $this->teamRepository->find($teamId);
            $user->addTeam($team);
        }
        $this->userRepository->save($user);

        return [];
    }
}
