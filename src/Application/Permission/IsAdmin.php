<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Permission;

use HexagonalPlayground\Domain\User;

class IsAdmin extends Permission
{
    public static function check(User $user): void
    {
        self::assertTrue(
            $user->hasRole(User::ROLE_ADMIN),
            'This action requires admin rights'
        );
    }
}