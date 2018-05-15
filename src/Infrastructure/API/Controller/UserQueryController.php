<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Domain\User;
use Slim\Http\Response;

class UserQueryController
{
    /** @var User */
    private $authenticatedUser;

    /**
     * @param User $authenticatedUser
     */
    public function __construct(User $authenticatedUser)
    {
        $this->authenticatedUser = $authenticatedUser;
    }

    /**
     * @return Response
     */
    public function getAuthenticatedUser(): Response
    {
        $user = $this->authenticatedUser;
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