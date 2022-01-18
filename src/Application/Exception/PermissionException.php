<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Exception;

use Exception;
use HexagonalPlayground\Domain\ExceptionInterface;

class PermissionException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-PERMISSION';

    public function getHttpResponseCode(): int
    {
        return 403; // Forbidden
    }
}
