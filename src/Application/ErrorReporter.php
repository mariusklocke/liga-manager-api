<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use Throwable;

interface ErrorReporter
{
    /**
     * Reports an error to the error reporting system.
     *
     * @param Throwable $exception
     */
    public function report(Throwable $exception): void;
}
