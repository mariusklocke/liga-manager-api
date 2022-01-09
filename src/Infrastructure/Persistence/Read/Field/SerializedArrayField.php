<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

class SerializedArrayField extends Field
{
    public function hydrate(array $row): array
    {
        return unserialize($row[$this->getName()]);
    }
}
