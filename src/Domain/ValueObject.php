<?php declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use ArrayIterator;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use LogicException;

abstract class ValueObject implements IteratorAggregate
{
    /**
     * @param ValueObject $other
     * @return bool
     */
    public function equals(ValueObject $other): bool
    {
        if (get_class($this) !== get_class($other)) {
            throw new InvalidArgumentException(sprintf(
                'Cannot compare an instance of %s to an instance of %s',
                get_class($this),
                get_class($other)
            ));
        }

        foreach (get_object_vars($this) as $property => $value) {
            if ($this->{$property} !== $other->{$property}) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach (get_object_vars($this) as $property => $value) {
            if (is_array($value) || is_object($value) || is_resource($value)) {
                throw new LogicException(sprintf(
                    'toArray() cannot handle property %s on instance of %s. Given type: %s',
                    $property,
                    get_class($this),
                    gettype($value)
                ));
            }

            $result[$property] = $value;
        }

        return $result;
    }

    /**
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator(get_object_vars($this));
    }
}