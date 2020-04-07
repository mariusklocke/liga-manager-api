<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Permission;

use HexagonalPlayground\Application\Exception\PermissionException;

abstract class Permission
{
    /**
     * @throws PermissionException if permission is not granted
     */
    abstract public function check(): void;

    /**
     * @param string $message
     * @throws PermissionException
     */
    protected function fail(string $message): void
    {
        throw new PermissionException($message);
    }
}