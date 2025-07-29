<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Exception;

/**
 * Exception is thrown if a user has insufficient permissions for an action
 */
class PermissionException extends LocalizableException implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-PERMISSION';
}
