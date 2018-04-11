<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use DateTimeImmutable;
use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;

trait DateParser
{
    /**
     * @param string $value
     * @return DateTimeImmutable
     * @throws BadRequestException
     */
    protected function parseDate($value): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage());
        }
    }
}