<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

use HexagonalPlayground\Domain\Exception\InvalidInputException;

class StringField extends Field
{
    public function hydrate(array $row): ?string
    {
        $value = $row[$this->getName()] ?? null;

        return $value !== null ? (string)$value : null;
    }

    public function validate(mixed $value): void
    {
        is_string($value) || throw new InvalidInputException('invalidDataType', [$this->getName(), 'string']);
    }
}
