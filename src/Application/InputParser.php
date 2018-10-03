<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Application\Value\DatePeriod;

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
        if (ctype_digit(ltrim($value, '-'))) {
            return (int) $value;
        }
        throw new InvalidInputException('Cannot parse integer. Got: ' . $value);
    }

    /**
     * Parses a float from a string
     *
     * @param string $value
     * @return float
     */
    public static function parseFloat(string $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        throw new InvalidInputException('Cannot parse float. Got: ' . $value);
    }

    /**
     * Parses a boolean from a string
     *
     * @param string $value
     * @return bool
     */
    public static function parseBoolean(string $value): bool
    {
        if ('0' === $value || '1' === $value) {
            return (bool) $value;
        }
        throw new InvalidInputException('Cannot parse boolean. Got: ' . $value);
    }

    /**
     * Parses a DateTime from a string
     *
     * @param string $value
     * @return \DateTimeImmutable
     */
    public static function parseDateTime(string $value): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception $e) {
            throw new InvalidInputException('Cannot parse date. Got: ' . $value);
        }
    }

    /**
     * Parses a DatePeriod from a string tuple
     *
     * @param array $datePeriod
     * @return DatePeriod
     * @throws InvalidInputException
     */
    public static function parseDatePeriod(array $datePeriod): DatePeriod
    {
        $from = $datePeriod['from'] ?? null;
        $to   = $datePeriod['to'] ?? null;
        if (!is_string($from)) {
            throw new InvalidInputException('Cannot parse date period. Missing or invalid property "from".');
        }
        if (!is_string($to)) {
            throw new InvalidInputException('Cannot parse date period. Missing or invalid property "to".');
        }

        return new DatePeriod(self::parseDateTime($from), self::parseDateTime($to));
    }
}