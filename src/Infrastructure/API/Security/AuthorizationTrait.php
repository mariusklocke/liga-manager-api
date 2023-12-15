<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use Psr\Http\Message\ServerRequestInterface;

trait AuthorizationTrait
{
    private function assertIsAdmin(ServerRequestInterface $request): void
    {
        $authReader = new AuthReader();
        $authContext = $authReader->requireAuthContext($request);
        $authContext->getUser()->assertIsAdmin();
    }
}
