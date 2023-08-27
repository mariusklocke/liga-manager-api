<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use HexagonalPlayground\Application\Security\AuthenticationException;
use HexagonalPlayground\Application\Security\AuthChecker;
use HexagonalPlayground\Application\Security\AuthContext;
use Psr\Http\Message\ServerRequestInterface;

class AuthReader
{
    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function hasAuthContext(ServerRequestInterface $request): bool
    {
        return $request->getAttribute('auth') !== null;
    }

    /**
     * @param ServerRequestInterface $request
     * @return AuthContext
     * @throws AuthenticationException
     */
    public function requireAuthContext(ServerRequestInterface $request): AuthContext
    {
        $checker = new AuthChecker();
        $context = $request->getAttribute('auth');
        $checker->check($context);

        return $context;
    }
}
