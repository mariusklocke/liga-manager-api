<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

class AuthChecker
{
    public function check(?AuthContext $authContext): void
    {
        null !== $authContext || throw new AuthenticationException('missingAuthentication');
    }
}
