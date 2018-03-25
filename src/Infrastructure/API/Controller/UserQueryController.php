<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Security\Authenticator;
use Slim\Http\Response;

class UserQueryController
{
    /** @var Authenticator */
    private $authenticator;

    /**
     * @param Authenticator $authenticator
     */
    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * @return Response
     */
    public function getAuthenticatedUser(): Response
    {
        $user = $this->authenticator->getAuthenticatedUser();
        return (new Response(200))->withJson($user->toArray());
    }
}