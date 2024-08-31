<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\Exception\InvalidFormat;

class CustomDateTimeType extends DateTimeImmutableType
{
    public const NAME = 'custom_datetime';

    /**
     * @var DateTimeZone|null
     */
    private static ?DateTimeZone $utc = null;

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

        $dateTime = DateTimeImmutable::createFromFormat($platform->getDateTimeFormatString(), $value, self::getUtc());

        if ($dateTime !== false) {
            return $dateTime;
        }

        throw new InvalidFormat(sprintf(
            'Could not convert database value "%s" to Doctrine Type %s. Expected format "%s".',
            strlen($value) > 32 ? substr($value, 0, 20) . '...' : $value,
            static::class,
            $expectedFormat ?? '',
        ));
    }

    private static function getUtc(): DateTimeZone
    {
        return self::$utc ?: self::$utc = new DateTimeZone('UTC');
    }
}
