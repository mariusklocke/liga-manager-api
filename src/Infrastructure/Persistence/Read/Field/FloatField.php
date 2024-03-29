<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

class FloatField extends Field
{
    public function hydrate(array $row): ?float
    {
        $value = $row[$this->getName()] ?? null;

        return $value !== null ? (float)$value : null;
    }
}
