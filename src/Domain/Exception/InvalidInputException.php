<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Exception;

/**
 * Exception is thrown if an input value is invalid
 */
class InvalidInputException extends LocalizableException implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-INVALID-INPUT';
}
