<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Exception;

use Exception;
use HexagonalPlayground\Domain\Exception\ExceptionInterface;

class InvalidInputException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-INVALID-INPUT';

    public function getHttpResponseCode(): int
    {
        return 400; // Bad Request
    }
}
