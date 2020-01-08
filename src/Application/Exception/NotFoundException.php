<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Exception;

use Exception;
use HexagonalPlayground\Domain\ExceptionInterface;

class NotFoundException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-NOT-FOUND';

    /**
     * Returns the appropriate HTTP response status code
     *
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return 404;
    }
}
