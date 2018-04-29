<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use UnexpectedValueException;

class EnvironmentProvider
{
    /**
     * Returns the value of a mandatory environment variable
     *
     * @param string $name
     * @return string
     */
    public static function getMandatory(string $name): string
    {
        $value = getenv($name);
        if (!is_string($value)) {
            throw new UnexpectedValueException('Missing mandatory environment variable: ' . $name);
        }
        return $value;
    }

    /**
     * Returns the value of an optional environment variable
     *
     * @param string $name
     * @return string|null
     */
    public static function getOptional(string $name)
    {
        $value = getenv($name);
        if (is_string($value)) {
            return $value;
        }
        return null;
    }
}