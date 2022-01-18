<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Exception;

use Exception;
use HexagonalPlayground\Domain\ExceptionInterface;

class NotFoundException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-NOT-FOUND';

    public function getHttpResponseCode(): int
    {
        return 404; // Not found
    }
}
