<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeImmutableType;

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
        $result = parent::convertToPHPValue($value, $platform);
        if ($result instanceof DateTimeImmutable) {
            return $result->setTimezone(self::getUtc());
        }

        return $result;
    }

    private static function getUtc(): DateTimeZone
    {
        return self::$utc ?: self::$utc = new DateTimeZone('UTC');
    }
}
