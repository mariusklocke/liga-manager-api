<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

use HexagonalPlayground\Application\TypeAssert;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

class DateTimeField extends Field
{
    private static ?DateTimeZone $utc = null;
    private static ?DateTimeZone $localTimeZone = null;

    private static function getUtc(): DateTimeZone
    {
        return self::$utc ?: self::$utc = new DateTimeZone('UTC');
    }

    private static function getLocalTimeZone(): DateTimeZone
    {
        return self::$localTimeZone ?: self::$localTimeZone = new DateTimeZone(date_default_timezone_get());
    }

    public function hydrate(array $row): ?string
    {
        $value = $row[$this->getName()] ?? null;

        if ($value === null) {
            return null;
        }

        return (new DateTimeImmutable($value, self::getUtc()))->setTimezone(self::getLocalTimeZone())->format(DATE_ATOM);
    }

    public function validate(mixed $value): void
    {
        TypeAssert::assertInstanceOf($value, DateTimeInterface::class, $this->getName());
    }
}
