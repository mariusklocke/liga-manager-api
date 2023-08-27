<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Exception;

use Exception;

/**
 * Exception is thrown in case of an internal error the client cannot fix
 */
class InternalException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-INTERNAL';

    public function getHttpResponseCode(): int
    {
        return 500; // Internal Server Error
    }
}
