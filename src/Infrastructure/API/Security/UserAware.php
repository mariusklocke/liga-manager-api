<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use HexagonalPlayground\Domain\User;
use Psr\Http\Message\ServerRequestInterface;

trait UserAware
{
    protected function getUserFromRequest(ServerRequestInterface $request): User
    {
        return $request->getAttribute('user');
    }
}