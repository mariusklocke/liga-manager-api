<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Util;

use HexagonalPlayground\Domain\Exception\DomainException;

class Assert
{
    private function __construct()
    {
        // Cannot be instantiated - static methods only
    }

    /**
     * Asserts that a value is contained in a whitelist
     *
     * @param mixed $value
     * @param array $whitelist
     * @param string $message
     * @throws DomainException
     */
    public static function oneOf($value, array $whitelist, string $message)
    {
        self::true(in_array($value, $whitelist, true), sprintf(
            $message,
            implode(',', $whitelist),
            $value
        ));
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
