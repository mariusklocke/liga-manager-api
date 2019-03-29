<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Permission;

use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class CanManageTeam extends Permission
{
    public static function check(Team $team, User $user): void
    {
        self::assertTrue(
            $user->hasRole(User::ROLE_ADMIN) || $user->isInTeam($team),
            'User is not permitted to manage this team'
        );
    }
}