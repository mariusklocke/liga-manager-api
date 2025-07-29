<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Exception;

/**
 * Exception is thrown if a required object could not be found
 */
class NotFoundException extends LocalizableException implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-NOT-FOUND';
}
