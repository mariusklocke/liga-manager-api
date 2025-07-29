<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Exception;

/**
 * Exception is thrown if a value violates a uniqueness constraint
 */
class UniquenessException extends LocalizableException implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-UNIQUENESS';
}
