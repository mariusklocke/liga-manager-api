<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

interface ExceptionInterface
{
    /**
     * Returns a precise message which is safe to expose to the client
     *
     * @return string
     */
    public function getMessage();

    /**
     * Returns an application-specific error code as string
     *
     * All error codes must be uppercase and start with "ERR-"
     *
     * @return string
     */
    public function getCode();
}