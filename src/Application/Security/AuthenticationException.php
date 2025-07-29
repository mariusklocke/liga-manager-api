<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use HexagonalPlayground\Domain\Exception\ExceptionInterface;
use HexagonalPlayground\Domain\Exception\LocalizableException;

class AuthenticationException extends LocalizableException implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-AUTHENTICATION';
}
