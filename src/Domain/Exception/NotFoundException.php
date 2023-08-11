<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Exception;

use Exception;

class NotFoundException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-NOT-FOUND';

    public function getHttpResponseCode(): int
    {
        return 404; // Not found
    }
}
