<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Exception;

use Exception;
use HexagonalPlayground\Domain\ExceptionInterface;

class AuthenticationException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-AUTHENTICATION';

    /**
     * Returns the appropriate HTTP response status code
     *
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return 401;
    }
}