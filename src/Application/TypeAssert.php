<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use HexagonalPlayground\Application\Exception\InvalidInputException;

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

    public static function assertNumberOrNull($value, string $name): void
    {
        if (!is_null($value) && !is_int($value) && !is_float($value)) {
            throw self::createException($name, $value, 'int|float|null');
        }
    }

    public static function assertInteger($value, string $name): void
    {
        if (!is_int($value)) {
            throw self::createException($name, $value, 'int');
        }
    }

    public static function assertIntegerOrNull($value, string $name): void
    {
        if (!is_null($value) && !is_int($value)) {
            throw self::createException($name, $value, 'int|null');
        }
    }

    public static function assertString($value, string $name): void
    {
        if (!is_string($value)) {
            throw self::createException($name, $value, 'string');
        }
    }

    public static function assertStringOrNull($value, string $name): void
    {
        if (!is_null($value) && !is_string($value)) {
            throw self::createException($name, $value, 'string|null');
        }
    }

    public static function assertBoolean($value, string $name): void
    {
        if (!is_bool($value)) {
            throw self::createException($name, $value, 'bool');
        }
    }

    public static function assertBooleanOrNull($value, string $name): void
    {
        if (!is_null($value) && !is_bool($value)) {
            throw self::createException($name, $value, 'bool|null');
        }
    }

    public static function assertArray($value, string $name): void
    {
        if (!is_array($value)) {
            throw self::createException($name, $value, 'array');
        }
    }

    public static function assertArrayOrNull($value, string $name): void
    {
        if (!is_null($value) && !is_array($value)) {
            throw self::createException($name, $value, 'array|null');
        }
    }

    private static function createException(string $name, $value, string $expectedType): InvalidInputException
    {
        $message = sprintf(
            'Invalid parameter type for %s. Expected: %s. Given: %s',
            $name,
            gettype($value),
            $expectedType
        );
        return new InvalidInputException($message);
    }
}