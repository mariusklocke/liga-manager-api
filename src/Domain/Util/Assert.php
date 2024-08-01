<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Util;

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
     * @param string $exceptionClass
     */
    public static function oneOf($value, array $whitelist, string $message, string $exceptionClass): void
    {
        if (!in_array($value, $whitelist, true)) {
            throw new $exceptionClass(sprintf($message, implode(',', $whitelist), $value));
        }
    }

    /**
     * Asserts that a boolean is false
     *
     * @param bool $value
     * @param string $message
     * @param string $exceptionClass
     */
    public static function false(bool $value, string $message, string $exceptionClass): void
    {
        if ($value) {
            throw new $exceptionClass($message);
        }
    }

    /**
     * Asserts that a boolean is true
     *
     * @param bool $value
     * @param string $message
     * @param string $exceptionClass
     */
    public static function true(bool $value, string $message, string $exceptionClass): void
    {
        if (!$value) {
            throw new $exceptionClass($message);
        }
    }
}
