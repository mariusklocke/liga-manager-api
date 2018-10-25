<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Permission;

use HexagonalPlayground\Application\Exception\PermissionException;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\User;

class CanChangeMatch
{
    public static function check(User $user, Match $match): void
    {
        if ($user->hasRole(User::ROLE_ADMIN)
            || $user->isInTeam($match->getHomeTeam())
            || $user->isInTeam($match->getGuestTeam())
        ) {
            return;
        }

        throw new PermissionException('User is not permitted to change this match');
    }
}