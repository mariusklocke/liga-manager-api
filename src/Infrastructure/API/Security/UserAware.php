<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use HexagonalPlayground\Application\Exception\AuthenticationException;
use HexagonalPlayground\Domain\User;
use Psr\Http\Message\ServerRequestInterface;

trait UserAware
{
    protected function getUserFromRequest(ServerRequestInterface $request): User
    {
        $user = $request->getAttribute('user');
        if ($user instanceof User) {
            return $user;
        }

        throw new AuthenticationException('Missing Authorization');
    }
}