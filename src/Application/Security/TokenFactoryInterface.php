<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use HexagonalPlayground\Domain\User;

interface TokenFactoryInterface
{
    /**
     * Create a new token for the given user
     *
     * @param User $user
     * @return TokenInterface
     */
    public function create(User $user): TokenInterface;
}