<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Infrastructure\API\ResponseFactoryTrait;
use HexagonalPlayground\Infrastructure\API\Security\UserAware;
use HexagonalPlayground\Infrastructure\Persistence\Read\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class UserQueryController
{
    use UserAware;
    use ResponseFactoryTrait;

    /** @var UserRepository */
    private $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Finds all registered users
     *
     * @param Request $request
     * @return ResponseInterface
     */
    public function findAllUsers(Request $request): ResponseInterface
    {
        $user = $this->getUserFromRequest($request);
        IsAdmin::check($user);

        return $this->createResponse(200, $this->userRepository->findAllUsers());
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function getAuthenticatedUser(Request $request): ResponseInterface
    {
        $user = $this->getUserFromRequest($request);
        return $this->createResponse(200, [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'teams' => $user->getTeamIds(),
            'role' => $user->getRole(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName()
        ]);
    }
}