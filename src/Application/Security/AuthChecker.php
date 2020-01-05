<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use HexagonalPlayground\Application\Exception\AuthenticationException;

class AuthChecker
{
    public function check(?AuthContext $authContext): void
    {
        if (null === $authContext) {
            throw new AuthenticationException('Missing Authentication');
        }
    }
}