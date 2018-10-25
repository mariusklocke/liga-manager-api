<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Permission;

use HexagonalPlayground\Application\Exception\PermissionException;
use HexagonalPlayground\Domain\User;

class IsAdmin
{
    public static function check(User $user): void
    {
        if (!$user->hasRole(User::ROLE_ADMIN)) {
            throw new PermissionException('This action requires admin rights');
        }
    }
}