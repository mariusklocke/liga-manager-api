<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use Exception;
use HexagonalPlayground\Domain\Exception\ExceptionInterface;

class RateLimitException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-RATE-LIMIT';

    public function getHttpResponseCode(): int
    {
        return 429; // Too Many Requests
    }
}
