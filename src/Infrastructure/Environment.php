<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

class Environment
{
    /**
     * Returns the string value for an environment variable
     *
     * @param string $name
     * @return string
     */
    public static function get(string $name): string
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Cannot get environment variable with empty name');
        }
        $value = getenv($name);
        if (false === $value) {
            throw new \RuntimeException('Cannot find environment variable ' . $name);
        }
        if (!is_string($value)) {
            throw new \UnexpectedValueException('Expected getenv() to return a string');
        }

        return $value;
    }
}