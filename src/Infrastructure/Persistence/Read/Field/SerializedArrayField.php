<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

class SerializedArrayField extends Field
{
    public function hydrate(array $row): array
    {
        return json_decode($row[$this->getName()], true);
    }

    public function validate(mixed $value): void
    {
        // TODO: Implement
    }
}
