<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Domain\ContactPerson;
use PHPUnit\Framework\TestCase;

class ValueObjectTest extends TestCase
{
    public function testCanBeCheckedForEquality(): void
    {
        $homer = new ContactPerson('Homer', 'Simpson', '12345', 'homer@example.com');
        self::assertTrue($homer->equals(clone $homer));

        $lisa = new ContactPerson('Lisa', 'Simpson', '12345', 'lisa@example.com');
        self::assertFalse($homer->equals($lisa));
        self::assertFalse($lisa->equals($homer));
    }

    public function testCanBeConvertedToArray(): void
    {
        $object = new ContactPerson('Homer', 'Simpson', '12345', 'homer@example.com');
        $array = $object->toArray();

        self::assertSame('Homer', $array['firstName']);
        self::assertSame('Simpson', $array['lastName']);
        self::assertSame('12345', $array['phone']);
        self::assertSame('homer@example.com', $array['email']);
    }

    public function testCanBeIterated(): void
    {
        $object = new ContactPerson('Homer', 'Simpson', '12345', 'homer@example.com');
        $iterator = $object->getIterator();

        $array = iterator_to_array($iterator);

        self::assertSame('Homer', $array['firstName']);
        self::assertSame('Simpson', $array['lastName']);
        self::assertSame('12345', $array['phone']);
        self::assertSame('homer@example.com', $array['email']);
    }
}
