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
     * Asserts that a boolean is false
     *
     * @param bool $value
     * @param string $exceptionClass
     * @param string $messageId
     * @param string $messageParams
     */
    public static function false(bool $value, string $exceptionClass, string $messageId, array $messageParams = []): void
    {
        if ($value) {
            throw new $exceptionClass($messageId, $messageParams);
        }
    }

    /**
     * Asserts that a boolean is true
     *
     * @param bool $value
     * @param string $exceptionClass
     * @param string $messageId
     * @param string $messageParams
     */
    public static function true(bool $value, string $exceptionClass, string $messageId, array $messageParams = []): void
    {
        if (!$value) {
            throw new $exceptionClass($messageId, $messageParams);
        }
    }
}
