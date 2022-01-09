<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

use DateTimeImmutable;
use DateTimeZone;

class DateTimeField extends Field
{
    /** @var DateTimeZone */
    private static $dateTimeZone;

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

        $string = (new DateTimeImmutable($value))
            ->setTimezone(self::getTimeZone())
            ->format(DATE_ATOM);

        // Adjust timezone identifier for not breaking tests
        return str_replace('+00:00', 'Z', $string);
    }
}
