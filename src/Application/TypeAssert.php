<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use HexagonalPlayground\Domain\Exception\InvalidInputException;

class TypeAssert
{
    private function __construct()
    {
        // Cannot be instantiated, static methods only
    }

    public static function assertNumber($value, string $name): void
    {
        if (!is_int($value) && !is_float($value)) {
            throw self::createException($name, $value, 'int|float');
        }
    }

    public static function assertInteger($value, string $name): void
    {
        if (!is_int($value)) {
            throw self::createException($name, $value, 'int');
        }
    }

    public static function assertString($value, string $name): void
    {
        if (!is_string($value)) {
            throw self::createException($name, $value, 'string');
        }
    }

    public static function assertArray($value, string $name): void
    {
        if (!is_array($value)) {
            throw self::createException($name, $value, 'array');
        }
    }

    public static function assertInstanceOf($value, $base, string $name): void
    {
        if (!($value instanceof $base)) {
            throw self::createException($name, $value, $base);
        }
    }

    private static function createException(string $name, $value, string $expectedType): InvalidInputException
    {
        return new InvalidInputException(sprintf(
            'Invalid type for input %s. Expected: %s. Given: %s',
            $name,
            $expectedType,
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }
}
