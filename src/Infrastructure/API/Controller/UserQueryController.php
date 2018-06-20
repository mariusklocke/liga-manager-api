<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Domain\User;
use Slim\Http\Request;
use Slim\Http\Response;

class UserQueryController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function getAuthenticatedUser(Request $request): Response
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
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