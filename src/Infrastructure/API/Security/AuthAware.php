<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use HexagonalPlayground\Application\Exception\AuthenticationException;
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
        $context = $this->getAuthContext($request);
        if (null === $context) {
            throw new AuthenticationException('Missing Authentication');
        }

        return $context;
    }
}