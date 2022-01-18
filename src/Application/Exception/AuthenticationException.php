<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Exception;

use Exception;
use HexagonalPlayground\Domain\ExceptionInterface;

class AuthenticationException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-AUTHENTICATION';

    public function getHttpResponseCode(): int
    {
        return 401; // Unauthorized
    }
}
