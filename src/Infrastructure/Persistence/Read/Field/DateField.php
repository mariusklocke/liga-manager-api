<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

use HexagonalPlayground\Application\TypeAssert;
use DateTimeImmutable;
use DateTimeInterface;

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

    public function validate(mixed $value): void
    {
        TypeAssert::assertInstanceOf($value, DateTimeInterface::class, $this->getName());
    }
}
