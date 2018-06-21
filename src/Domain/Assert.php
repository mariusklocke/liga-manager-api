<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

class Assert
{
    private function __construct()
    {
        // Cannot be instantiated - static methods only
    }

    public static function minLength(string $value, int $minLength, string $message): void
    {
        if (mb_strlen($value) < $minLength) {
            throw new DomainException($message);
        }
    }

    public static function maxLength(string $value, int $maxLength, string $message): void
    {
        if (mb_strlen($value) > $maxLength) {
            throw new DomainException($message);
        }
    }

    public static function greatherThan($value, $limit, string $message): void
    {
        if ($value <= $limit) {
            throw new DomainException($message);
        }
    }

    public static function greaterOrEqualThan($value, $limit, string $message): void
    {
        if ($value < $limit) {
            throw new DomainException($message);
        }
    }

    public static function lessThan($value, $limit, string $message): void
    {
        if ($value >= $limit) {
            throw new DomainException($message);
        }
    }

    public static function lessOrEqualThan($value, $limit, string $message): void
    {
        if ($value > $limit) {
            throw new DomainException($message);
        }
    }

    public static function emailAddress(string $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Invalid email syntax');
        }
    }

    public static function oneOf($value, array $whitelist, string $message)
    {
        if (!in_array($value, $whitelist)) {
            throw new DomainException(sprintf(
                $message,
                implode(',', $whitelist),
                $value
            ));
        }
    }
}