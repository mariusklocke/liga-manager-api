<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Permission;

use HexagonalPlayground\Domain\MatchEntity;
use HexagonalPlayground\Domain\User;

class CanChangeMatch extends Permission
{
    /** @var User */
    private User $user;

    /** @var MatchEntity */
    private MatchEntity $match;

    /**
     * @param User $user
     * @param MatchEntity $match
     */
    public function __construct(User $user, MatchEntity $match)
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
