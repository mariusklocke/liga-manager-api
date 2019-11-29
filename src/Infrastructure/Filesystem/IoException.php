<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

use HexagonalPlayground\Domain\ExceptionInterface;
use RuntimeException;

class IoException extends RuntimeException implements ExceptionInterface
{
    /**
     * Returns the appropriate HTTP response status code
     *
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return 500;
    }
}