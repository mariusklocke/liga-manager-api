<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Permission;

use HexagonalPlayground\Application\Exception\PermissionException;

abstract class Permission
{
    protected static function assertTrue(bool $value, string $message)
    {
        if (!$value) {
            throw new PermissionException($message);
        }
    }
}