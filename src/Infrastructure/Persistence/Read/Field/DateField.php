<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

use DateTimeImmutable;

class DateField extends Field
{
    public function hydrate(array $row): ?string
    {
        $value = $row[$this->getName()] ?? null;

        if ($value === null) {
            return null;
        }

        return (new DateTimeImmutable($value))->format('Y-m-d');
    }
}
