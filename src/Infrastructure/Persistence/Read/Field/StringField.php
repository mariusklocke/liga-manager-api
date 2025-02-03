<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

use HexagonalPlayground\Application\TypeAssert;

class StringField extends Field
{
    public function hydrate(array $row): ?string
    {
        $value = $row[$this->getName()] ?? null;

        return $value !== null ? (string)$value : null;
    }

    public function validate(mixed $value): void
    {
        TypeAssert::assertString($value, $this->getName());
    }
}
