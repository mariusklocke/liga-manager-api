<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DateTimeImmutable;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Application\Security\TokenInterface;
use HexagonalPlayground\Domain\User;

class JsonWebTokenFactory implements TokenFactoryInterface
{
    /**
     * @param User $user
     * @param DateTimeImmutable $expiresAt
     * @return JsonWebToken
     */
    public function create(User $user, DateTimeImmutable $expiresAt): TokenInterface
    {
        return new JsonWebToken($user->getId(), new DateTimeImmutable(), $expiresAt);
    }
}