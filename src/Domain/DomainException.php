<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Exception;

class DomainException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-DOMAIN';

    public function getHttpResponseCode(): int
    {
        return 400; // Bad Request
    }
}
