<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use DateTimeImmutable;
use Exception;
use HexagonalPlayground\Domain\Exception\InvalidInputException;

class InputParser
{
    private function __construct()
    {
        // Cannot be instantiated, static methods only
    }

    /**
     * Parses an integer from a string
     *
     * @param string $value
     * @return int
     */
    public static function parseInteger(string $value): int
    {
        ctype_digit(ltrim($value, '-')) || throw new InvalidInputException('invalidDataFormat', [$value, 'integer']);

        return (int) $value;
    }

    /**
     * Parses a Date from a string
     *
     * @param string $value
     * @return DateTimeImmutable
     */
    public static function parseDate(string $value): DateTimeImmutable
    {
        return self::parseDateTime($value)->setTime(0, 0);
    }

    /**
     * Parses a DateTime from a string
     *
     * @param string $value
     * @return DateTimeImmutable
     */
    public static function parseDateTime(string $value): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (Exception $e) {
            throw new InvalidInputException('invalidDataFormat', [$value, 'DateTime']);
        }
    }
}
