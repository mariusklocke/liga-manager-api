<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Exception;

use Exception;

class UniquenessException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-UNIQUENESS';

    public function getHttpResponseCode(): int
    {
        return 400; // Bad Request
    }
}
