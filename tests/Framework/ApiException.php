<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use Exception;

class ApiException extends Exception
{
    public function __construct(string $message = "", int $code = 0)
    {
        parent::__construct($message, $code);
    }
}