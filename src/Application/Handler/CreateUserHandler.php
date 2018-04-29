<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Exception\PermissionException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Exception\UniquenessException;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Application\Security\Authenticator;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class CreateUserHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var OrmRepositoryInterface */
    private $teamRepository;

    /** @var Authenticator */
    private $authenticator;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param OrmRepositoryInterface $teamRepository
     * @param Authenticator $authenticator
     */
    public function __construct(UserRepositoryInterface $userRepository, OrmRepositoryInterface $teamRepository, Authenticator $authenticator)
    {
        $this->userRepository = $userRepository;
        $this->teamRepository = $teamRepository;
        $this->authenticator = $authenticator;
    }

    /**
     * @param CreateUserCommand $command
     * @return string
     */
    public function handle(CreateUserCommand $command)
    {
        $this->checkPermissions($command);
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
     * @param CreateUserCommand $command
     * @throws PermissionException
     */
    private function checkPermissions(CreateUserCommand $command)
    {
        $authenticatedUser = $this->authenticator->getAuthenticatedUser();
        if ($authenticatedUser->hasRole(User::ROLE_ADMIN)) {
            return;
        }

        if ($authenticatedUser->hasRole(User::ROLE_TEAM_MANAGER)) {
            if ($command->getRole() !== User::ROLE_TEAM_MANAGER) {
                throw new PermissionException("Authenticated user can only create users with role 'team_manager'");
            }

            $permittedTeamIds = array_flip($authenticatedUser->getTeamIds());
            foreach ($command->getTeamIds() as $teamId) {
                if (!isset($permittedTeamIds[$teamId])) {
                    throw new PermissionException(sprintf(
                        "Authenticated user is not permitted to create users for team '%s'",
                        $teamId
                    ));
                }
            }
            return;
        }

        throw new PermissionException('Authenticated user is not permitted to create users');
    }

    /**
     * @param string $email
     * @throws UniquenessException
     */
    private function assertEmailDoesNotExist(string $email)
    {
        try {
            $this->userRepository->findByEmail($email);
        } catch (NotFoundException $e) {
            return;
        }

        throw new UniquenessException(
            sprintf("A user with email address '%s' already exists", $email)
        );
    }
}