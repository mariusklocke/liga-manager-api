<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

class InputParser
{
    /**
     * Parses an integer from a string
     *
     * @param string $value
     * @return int
     */
    public function parseInteger(string $value): int
    {
        if (ctype_digit(ltrim($value, '-'))) {
            return (int) $value;
        }
        throw new \InvalidArgumentException('Cannot parse integer. Got: %s' . $value);
    }

    /**
     * Parses a float from a string
     *
     * @param string $value
     * @return float
     */
    public function parseFloat(string $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        throw new \InvalidArgumentException('Cannot parse float. Got: %s' . $value);
    }

    /**
     * Parses a boolean from a string
     *
     * @param string $value
     * @return bool
     */
    public function parseBoolean(string $value): bool
    {
        if ('0' === $value || '1' === $value) {
            return (bool) $value;
        }
        throw new \InvalidArgumentException('Cannot parse boolean. Got: %s' . $value);
    }
}