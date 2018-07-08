<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Exception;

use Exception;
use HexagonalPlayground\Domain\ExceptionInterface;

class InvalidInputException extends Exception implements ExceptionInterface
{
    public function getHttpStatusCode(): int
    {
        return 400;
    }
}
