<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

class IntegerField extends Field
{
    public function hydrate(array $row): ?int
    {
        $value = $row[$this->getName()] ?? null;

        return $value !== null ? (int)$value : null;
    }
}
