<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use DateTimeImmutable;
use HexagonalPlayground\Domain\User;

interface TokenFactoryInterface
{
    /**
     * Create a new token for the given user
     *
     * @param User $user
     * @param DateTimeImmutable $expiresAt
     * @return TokenInterface
     */
    public function create(User $user, DateTimeImmutable $expiresAt): TokenInterface;
}