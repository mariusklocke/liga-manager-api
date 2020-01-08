<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Exception;

use Exception;
use HexagonalPlayground\Domain\ExceptionInterface;

class PermissionException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-PERMISSION';

    /**
     * Returns the appropriate HTTP response status code
     *
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return 403;
    }
}