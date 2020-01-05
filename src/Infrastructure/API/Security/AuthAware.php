<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use HexagonalPlayground\Application\Security\AuthChecker;
use HexagonalPlayground\Application\Security\AuthContext;
use Psr\Http\Message\ServerRequestInterface;

trait AuthAware
{
    public function getAuthContext(ServerRequestInterface $request): ?AuthContext
    {
        return $request->getAttribute('auth');
    }

    public function requireAuthContext(ServerRequestInterface $request): AuthContext
    {
        $checker = new AuthChecker();
        $context = $this->getAuthContext($request);
        $checker->check($context);

        return $context;
    }
}