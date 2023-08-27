<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Exception;

use Exception;

/**
 * Exception is thrown if an input value is invalid
 */
class InvalidInputException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-INVALID-INPUT';

    public function getHttpResponseCode(): int
    {
        return 400; // Bad Request
    }
}
