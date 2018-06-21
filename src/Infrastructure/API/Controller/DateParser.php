<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use DateTimeImmutable;
use HexagonalPlayground\Infrastructure\API\HttpException;

trait DateParser
{
    /**
     * @param string $value
     * @return DateTimeImmutable
     */
    protected function parseDate($value): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (\Exception $e) {
            throw HttpException::createBadRequest('Invalid date format');
        }
    }
}