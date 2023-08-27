<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Exception;
use HexagonalPlayground\Domain\Exception\ExceptionInterface;

class MaintenanceModeException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-MAINTENANCE-MODE';

    public function getHttpResponseCode()
    {
        return 503; // Service Unavailable
    }
}
