<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\User;

trait AuthenticationAware
{
    /** @var User */
    private $authenticatedUser;

    /**
     * @return User
     */
    public function getAuthenticatedUser(): User
    {
        return $this->authenticatedUser;
    }

    /**
     * @param User $user
     * @return AuthenticationAware
     */
    public function withAuthenticatedUser(User $user): self
    {
        $clone = clone $this;
        $clone->authenticatedUser = $user;

        return $clone;
    }
}