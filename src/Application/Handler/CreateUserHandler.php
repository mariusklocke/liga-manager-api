<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Exception\UniquenessException;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Application\Security\PermissionChecker;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class CreateUserHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var OrmRepositoryInterface */
    private $teamRepository;

    /** @var PermissionChecker */
    private $permissionChecker;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param OrmRepositoryInterface $teamRepository
     * @param PermissionChecker $permissionChecker
     */
    public function __construct(UserRepositoryInterface $userRepository, OrmRepositoryInterface $teamRepository, PermissionChecker $permissionChecker)
    {
        $this->userRepository    = $userRepository;
        $this->teamRepository    = $teamRepository;
        $this->permissionChecker = $permissionChecker;
    }

    /**
     * @param CreateUserCommand $command
     * @return string
     */
    public function handle(CreateUserCommand $command)
    {
        $this->permissionChecker->assertCanCreateUser($command);
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