<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Exception\InvalidInputException;

/**
 * @deprecated Use Application layer class instead
 */
trait TypeAssert
{
    /**
     * @param string $key
     * @param mixed  $value
     * @throws InvalidInputException
     */
    protected function assertNumber(string $key, $value): void
    {
        if (!is_int($value) && !is_float($value)) {
            $this->invalidType($key, $value, 'int|float');
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @throws InvalidInputException
     */
    protected function assertInteger(string $key, $value): void
    {
        if (!is_int($value)) {
            $this->invalidType($key, $value, 'int');
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @throws InvalidInputException
     */
    protected function assertString(string $key, $value): void
    {
        if (!is_string($value)) {
            $this->invalidType($key, $value, 'string');
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @throws InvalidInputException
     */
    protected function assertBoolean(string $key, $value): void
    {
        if (!is_bool($value)) {
            $this->invalidType($key, $value, 'bool');
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @throws InvalidInputException
     */
    protected function assertArray(string $key, $value): void
    {
        if (!is_array($value)) {
            $this->invalidType($key, $value, 'array');
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param string $expectedType
     * @throws InvalidInputException
     */
    private function invalidType(string $key, $value, string $expectedType): void
    {
        throw new InvalidInputException(sprintf(
            'Invalid request parameter type for %s. Expected: %s. Given: %s',
            $key,
            $expectedType,
            gettype($value)
        ));
    }
}