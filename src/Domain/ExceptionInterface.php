<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

interface ExceptionInterface
{
    /**
     * @return string
     */
    public function getMessage();

    /**
     * Returns the appropriate HTTP response status code
     *
     * @return int
     */
    public function getHttpStatusCode(): int;
}