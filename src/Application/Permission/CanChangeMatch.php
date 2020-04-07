<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Permission;

use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\User;

class CanChangeMatch extends Permission
{
    /** @var User */
    private $user;

    /** @var Match */
    private $match;

    /**
     * @param User $user
     * @param Match $match
     */
    public function __construct(User $user, Match $match)
    {
        $this->user = $user;
        $this->match = $match;
    }

    public function check(): void
    {
        if ($this->user->hasRole(User::ROLE_ADMIN)
            || $this->user->isInTeam($this->match->getHomeTeam())
            || $this->user->isInTeam($this->match->getGuestTeam())) {
            return;
        }

        $this->fail('User is not permitted to change this match');
    }
}