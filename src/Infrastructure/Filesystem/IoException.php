<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

use HexagonalPlayground\Domain\ExceptionInterface;
use RuntimeException;

class IoException extends RuntimeException implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-IO';

    public function getHttpResponseCode(): int
    {
        return 500; // Internal server error
    }
}
