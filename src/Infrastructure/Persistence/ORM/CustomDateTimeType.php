<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;

class CustomDateTimeType extends DateTimeImmutableType
{
    public const NAME = 'custom_datetime';

    private static ?DateTimeZone $utc = null;
    private static ?DateTimeZone $localTimeZone = null;

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof DateTimeImmutable) {
            $value = $value->setTimezone(self::getUtc());
        }
        return parent::convertToDatabaseValue($value, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?DateTimeImmutable
    {
        if ($value === null || $value instanceof DateTimeImmutable) {
            return $value;
        }

        is_string($value) || throw InvalidType::new(
            $value,
            static::class,
            ['null', 'string', DateTimeImmutable::class],
        );

        $dateTime = DateTimeImmutable::createFromFormat($platform->getDateTimeFormatString(), $value, self::getUtc());
        
        $dateTime !== false || throw new InvalidFormat(sprintf(
            'Could not convert database value "%s" to type %s. Expected format "%s".',
            strlen($value) > 32 ? substr($value, 0, 20) . '...' : $value,
            DateTimeImmutable::class,
            $platform->getDateTimeFormatString(),
        ));

        return $dateTime->setTimezone(self::getLocalTimeZone());
    }

    private static function getUtc(): DateTimeZone
    {
        return self::$utc ?: self::$utc = new DateTimeZone('UTC');
    }

    private static function getLocalTimeZone(): DateTimeZone
    {
        return self::$localTimeZone ?: self::$localTimeZone = new DateTimeZone(date_default_timezone_get());
    }
}
