<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use Ramsey\Uuid\Uuid;

class DataGenerator
{
    public static function generateId(): string
    {
        return Uuid::uuid4()->toString();
    }

    public static function generateEmail(): string
    {
        return self::generateId() . '@example.com';
    }

    public static function generatePassword(int $length = 16): string
    {
        $characters = array_merge(
            range('0', '9'),
            range('a', 'z'),
            range('A', 'Z')
        );

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, count($characters) - 1)];
        }

        return $password;
    }

    public static function generateFloat(): float
    {
        return mt_rand() / mt_getrandmax();
    }

    public static function generateLatitude(): float
    {
        $value = self::generateFloat() * 90;

        if (mt_rand() % 2 === 0) {
            $value *= -1;
        }

        return $value;
    }

    public static function generateLongitude(): float
    {
        $value = self::generateFloat() * 180;

        if (mt_rand() % 2 === 0) {
            $value *= -1;
        }

        return $value;
    }

    public static function generateBytes(int $length): string
    {
        return random_bytes($length);
    }

    public static function generateImage(string $format, int $width = 16, int $height = 16): File
    {
        $file = File::temp('random_image_', '.' . $format);
        $image = imagecreatetruecolor($width, $height);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $red = random_int(0, 255);
                $green = random_int(0, 255);
                $blue = random_int(0, 255);
                $color = imagecolorallocate($image, $red, $green, $blue);
	            imagesetpixel($image, $x, $y, $color);
            }
        }
        
        switch ($format) {
            case 'gif':
                imagegif($image, $file->getPath());
                break;
            case 'jpg':
                imagejpeg($image, $file->getPath());
                break;
            case 'png':
                imagepng($image, $file->getPath());
                break;
            case 'webp':
                imagewebp($image, $file->getPath());
                break;
        }

        return $file;
    }
}
