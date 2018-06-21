<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Exception\PermissionException;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Domain\DomainException;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class CreateUserHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var OrmRepositoryInterface */
    private $teamRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param OrmRepositoryInterface $teamRepository
     */
    public function __construct(UserRepositoryInterface $userRepository, OrmRepositoryInterface $teamRepository)
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
        $this->checkPermission($command);
        $this->assertEmailDoesNotExist($command->getEmail());
        $user = new User($command->getEmail(), $command->getPassword(), $command->getFirstName(), $command->getLastName());
        $user->setRole($command->getRole());
        foreach ($command->getTeamIds() as $teamId) {
            /** @var Team $team */
            $team = $this->teamRepository->find($teamId);
            $user->addTeam($team);
        }
        $this->userRepository->save($user);
        return $user->getId();
    }

    /**
     * @param string $email
     * @throws DomainException
     */
    private function assertEmailDoesNotExist(string $email)
    {
        try {
            $this->userRepository->findByEmail($email);
        } catch (NotFoundException $e) {
            return;
        }

        throw new DomainException(
            sprintf("A user with email address %s already exists", $email)
        );
    }

    /**
     * @param CreateUserCommand $command
     * @throws PermissionException
     */
    private function checkPermission(CreateUserCommand $command): void
    {
        if ($command->getAuthenticatedUser()->hasRole(User::ROLE_ADMIN)) {
            return;
        }

        if ($command->getAuthenticatedUser()->hasRole(User::ROLE_TEAM_MANAGER)) {
            if ($command->getRole() !== User::ROLE_TEAM_MANAGER) {
                throw new PermissionException("User can only create users with role 'team_manager'");
            }

            $permittedTeamIds = array_flip($command->getAuthenticatedUser()->getTeamIds());
            foreach ($command->getTeamIds() as $teamId) {
                if (!isset($permittedTeamIds[$teamId])) {
                    throw new PermissionException(sprintf(
                        "User is not permitted to create users for team '%s'",
                        $teamId
                    ));
                }
            }
            return;
        }

        throw new PermissionException('User is not permitted to create users');
    }
}