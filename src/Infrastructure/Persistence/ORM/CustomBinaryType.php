<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BinaryType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;

class CustomBinaryType extends BinaryType
{
    public const NAME = 'custom_binary';

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        throw ValueNotConvertible::new($value, 'custom_binary');
    }
}
