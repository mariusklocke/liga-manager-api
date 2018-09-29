<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Infrastructure\API\Security\UserAware;
use Slim\Http\Request;
use Slim\Http\Response;

class UserQueryController
{
    use UserAware;

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