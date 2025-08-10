<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

use HexagonalPlayground\Domain\Exception\InvalidInputException;

class FloatField extends Field
{
    public function hydrate(array $row): ?float
    {
        $value = $row[$this->getName()] ?? null;

        return $value !== null ? (float)$value : null;
    }

    public function validate(mixed $value): void
    {
        is_float($value) || is_int($value) || throw new InvalidInputException('invalidDataType', [$this->getName(), 'float|int']);
    }
}
