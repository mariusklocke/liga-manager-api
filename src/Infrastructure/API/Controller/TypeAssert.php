<?php

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;

trait TypeAssert
{
    /**
     * @param string $key
     * @param mixed  $value
     * @throws BadRequestException
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
     * @throws BadRequestException
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
     * @throws BadRequestException
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
     * @throws BadRequestException
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
     * @throws BadRequestException
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
     * @throws BadRequestException
     */
    private function invalidType(string $key, $value, string $expectedType): void
    {
        throw new BadRequestException(sprintf(
            'Invalid request parameter type for %s. Expected: %s. Given: %s',
            $key,
            $expectedType,
            gettype($value)
        ));
    }
}