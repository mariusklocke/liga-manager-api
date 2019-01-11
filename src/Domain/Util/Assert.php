<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Util;

use HexagonalPlayground\Domain\DomainException;

class Assert
{
    private function __construct()
    {
        // Cannot be instantiated - static methods only
    }

    /**
     * Asserts that a string is not shorter than a given amount of characters
     *
     * @param string $value
     * @param int $minLength
     * @param string $message
     * @throws DomainException
     */
    public static function minLength(string $value, int $minLength, string $message): void
    {
        if (mb_strlen($value) < $minLength) {
            self::throwException($message);
        }
    }

    /**
     * Asserts that a string is not longer than a given amount of characters
     *
     * @param string $value
     * @param int $maxLength
     * @param string $message
     * @throws DomainException
     */
    public static function maxLength(string $value, int $maxLength, string $message): void
    {
        if (mb_strlen($value) > $maxLength) {
            self::throwException($message);
        }
    }

    /**
     * Asserts that a string is a valid email address
     *
     * @param string $value
     * @throws DomainException
     */
    public static function emailAddress(string $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            self::throwException('Invalid email syntax');
        }
    }

    /**
     * Asserts that a value is contained in a whitelist
     *
     * @param $value
     * @param array $whitelist
     * @param string $message
     * @throws DomainException
     */
    public static function oneOf($value, array $whitelist, string $message)
    {
        if (!in_array($value, $whitelist, true)) {
            self::throwException(sprintf(
                $message,
                implode(',', $whitelist),
                $value
            ));
        }
    }

    /**
     * Asserts that a boolean is false
     *
     * @param bool $value
     * @param string $message
     * @throws DomainException
     */
    public static function false(bool $value, string $message): void
    {
        if ($value) {
            self::throwException($message);
        }
    }

    /**
     * Asserts that a boolean is true
     *
     * @param bool $value
     * @param string $message
     * @throws DomainException
     */
    public static function true(bool $value, string $message): void
    {
        if (!$value) {
            self::throwException($message);
        }
    }

    /**
     * @param string $message
     * @throws DomainException
     */
    private static function throwException(string $message): void
    {
        throw new DomainException($message);
    }
}