<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

use HexagonalPlayground\Domain\Exception\InvalidInputException;

class IntegerField extends Field
{
    public function hydrate(array $row): ?int
    {
        $value = $row[$this->getName()] ?? null;

        return $value !== null ? (int)$value : null;
    }

    public function validate(mixed $value): void
    {
        is_int($value) || throw new InvalidInputException('invalidDataType', [$this->getName(), 'int']);
    }
}
