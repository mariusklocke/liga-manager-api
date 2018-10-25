<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Permission;

use HexagonalPlayground\Application\Exception\PermissionException;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class CanManageTeam
{
    public static function check(Team $team, User $user): void
    {
        if ($user->hasRole(User::ROLE_ADMIN) || $user->isInTeam($team)) {
            return;
        }

        throw new PermissionException('User is not permitted to manage this team');
    }
}