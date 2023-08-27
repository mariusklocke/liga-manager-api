<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use Exception;
use HexagonalPlayground\Domain\Exception\ExceptionInterface;

class AuthenticationException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-AUTHENTICATION';

    public function getHttpResponseCode(): int
    {
        return 401; // Unauthorized
    }
}
