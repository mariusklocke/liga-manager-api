<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Exception;

use Exception;

/**
 * Exception is thrown if an action conflicts with the current state of an object
 */
class ConflictException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-CONFLICT';

    public function getHttpResponseCode(): int
    {
        return 409; // Conflict
    }
}
