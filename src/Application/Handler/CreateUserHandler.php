<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Exception\AuthorizationException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Exception\UniquenessException;
use HexagonalPlayground\Application\Factory\UserFactory;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Application\Security\Authenticator;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class CreateUserHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var UserFactory */
    private $userFactory;

    /** @var OrmRepositoryInterface */
    private $teamRepository;

    /** @var Authenticator */
    private $authenticator;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param UserFactory $userFactory
     * @param OrmRepositoryInterface $teamRepository
     * @param Authenticator $authenticator
     */
    public function __construct(UserRepositoryInterface $userRepository, UserFactory $userFactory, OrmRepositoryInterface $teamRepository, Authenticator $authenticator)
    {
        $this->userRepository = $userRepository;
        $this->userFactory = $userFactory;
        $this->teamRepository = $teamRepository;
        $this->authenticator = $authenticator;
    }

    /**
     * @param CreateUserCommand $command
     * @return string
     */
    public function handle(CreateUserCommand $command)
    {
        $this->checkPermissions();
        $this->assertEmailDoesNotExist($command->getEmail());
        $user = $this->userFactory->createUser($command->getEmail(), $command->getPassword(), $command->getFirstName(), $command->getLastName());
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
     * @throws AuthorizationException
     */
    private function checkPermissions()
    {
        $requiredRole = User::ROLE_ADMIN;
        if (!$this->authenticator->getAuthenticatedUser()->hasRole($requiredRole)) {
            throw new AuthorizationException(sprintf(
                "Users can only be created from users with role '%s'", $requiredRole)
            );
        }
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
            sprintf('A user with email address "%s" already exists', $email)
        );
    }
}