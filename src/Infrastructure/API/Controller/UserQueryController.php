<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Infrastructure\API\Security\UserAware;
use HexagonalPlayground\Infrastructure\Persistence\Read\UserRepository;
use Slim\Http\Request;
use Slim\Http\Response;

class UserQueryController
{
    use UserAware;

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
     * @return Response
     */
    public function findAllUsers(Request $request): Response
    {
        $user = $this->getUserFromRequest($request);
        IsAdmin::check($user);

        return (new Response(200))->withJson($this->userRepository->findAllUsers());
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getAuthenticatedUser(Request $request): Response
    {
        $user = $this->getUserFromRequest($request);
        return (new Response(200))->withJson([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'teams' => $user->getTeamIds(),
            'role' => $user->getRole(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName()
        ]);
    }
}