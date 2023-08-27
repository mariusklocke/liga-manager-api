<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Exception;

use Exception;

/**
 * Exception is thrown if a required object could not be found
 */
class NotFoundException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-NOT-FOUND';

    public function getHttpResponseCode(): int
    {
        return 404; // Not found
    }
}
