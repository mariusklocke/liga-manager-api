<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Factory\UserFactory;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;

class CreateUserHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var UserFactory */
    private $userFactory;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param UserFactory $userFactory
     */
    public function __construct(UserRepositoryInterface $userRepository, UserFactory $userFactory)
    {
        $this->userRepository = $userRepository;
        $this->userFactory    = $userFactory;
    }

    public function handle(CreateUserCommand $command)
    {
        $user = $this->userFactory->createUser($command->getEmail(), $command->getEmail());
        $this->userRepository->save($user);
        return $user->getId();
    }
}