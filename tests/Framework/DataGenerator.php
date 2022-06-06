<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use Ramsey\Uuid\Uuid;
use stdClass;

class DataGenerator
{
    public static function generateEmail(): string
    {
        return self::generateString(8) . '@example.com';
    }

    public static function generateFloat(float $min, float $max): float
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    public static function generateGeoLocation(): object
    {
        $location = new stdClass();
        $location->latitude = DataGenerator::generateFloat(-90, 90);
        $location->longitude = DataGenerator::generateFloat(-180, 180);

        return $location;
    }

    public static function generateId(): string
    {
        return Uuid::uuid4()->toString();
    }

    public static function generatePassword(): string
    {
        return self::generateString(16);
    }

    public static function generateString(int $length): string
    {
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= chr(mt_rand(97, 122)); // lowercase a-z
        }

        return $string;
    }
}
