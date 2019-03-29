<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Permission;

use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\User;

class CanChangeMatch extends Permission
{
    public static function check(User $user, Match $match): void
    {
        self::assertTrue(
            $user->hasRole(User::ROLE_ADMIN)
                || $user->isInTeam($match->getHomeTeam())
                || $user->isInTeam($match->getGuestTeam()),
            'User is not permitted to change this match'
        );
    }
}