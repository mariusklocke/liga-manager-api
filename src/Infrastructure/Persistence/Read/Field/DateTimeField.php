<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

use HexagonalPlayground\Application\TypeAssert;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

class DateTimeField extends Field
{
    /** @var DateTimeZone|null */
    private static ?DateTimeZone $dateTimeZone = null;

    private static function getTimeZone(): DateTimeZone
    {
        if (self::$dateTimeZone === null) {
            self::$dateTimeZone = new DateTimeZone('UTC');
        }

        return self::$dateTimeZone;
    }

    public function hydrate(array $row): ?string
    {
        $value = $row[$this->getName()] ?? null;

        if ($value === null) {
            return null;
        }

        $string = (new DateTimeImmutable($value, self::getTimeZone()))->format(DATE_ATOM);

        // Adjust timezone identifier for not breaking tests
        return str_replace('+00:00', 'Z', $string);
    }

    public function validate(mixed $value): void
    {
        TypeAssert::assertInstanceOf($value, DateTimeInterface::class, $this->getName());
    }
}
