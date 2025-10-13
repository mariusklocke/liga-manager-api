<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Infrastructure\API\Network\IpAddress;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class IpAddressTest extends TestCase
{
    #[DataProvider('provideValidAddresses')]
    public function testValidAddresses(string $address, int $version, bool $private, bool $local): void
    {
        $subject = new IpAddress($address);
        self::assertSame($address, (string)$subject);
        self::assertSame($version, $subject->version);
        self::assertSame($private, $subject->isPrivate());
        self::assertSame($local, $subject->isLocal());
    }

    #[DataProvider('provideInvalidAddresses')]
    public function testInvalidAddresses(string $address): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IpAddress($address);
    }

    public static function provideValidAddresses(): array
    {
        return [
            ['10.31.79.144', 4, true, false],
            ['12.92.254.1', 4, false, false],
            ['127.0.0.1', 4, false, true],
            ['172.18.0.1', 4, true, false],
            ['172.32.0.1', 4, false, false],
            ['192.168.0.12', 4, true, false],
            ['::1', 6, false, true],
            ['fd62:8d0e:9bbe:1::1', 6, true, false]
        ];
    }

    public static function provideInvalidAddresses(): array
    {
        return [
            [''],
            ['256.256.256.256'],
            ['foobar'],
        ];
    }
}