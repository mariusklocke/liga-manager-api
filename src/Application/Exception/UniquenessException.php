<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Exception;

use Exception;
use HexagonalPlayground\Domain\ExceptionInterface;

class UniquenessException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-UNIQUENESS';
}