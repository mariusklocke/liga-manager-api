<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Domain\Value\GeographicLocation;
use PHPUnit\Framework\TestCase;

class ValueObjectTest extends TestCase
{
    private GeographicLocation $waltersHouse;

    private GeographicLocation $whiteHouse;

    protected function setUp(): void
    {
        $this->waltersHouse = new GeographicLocation(-106.536548, 35.126091);
        $this->whiteHouse = new GeographicLocation(-77.036578,38.897580);
    }

    public function testCanBeCheckedForEquality(): void
    {
        self::assertTrue($this->waltersHouse->equals(clone $this->waltersHouse));
        self::assertFalse($this->waltersHouse->equals($this->whiteHouse));
        self::assertFalse($this->whiteHouse->equals($this->waltersHouse));
    }

    public function testCanBeConvertedToArray(): void
    {
        $array = $this->whiteHouse->toArray();

        self::assertSame($this->whiteHouse->getLatitude(), $array['latitude']);
        self::assertSame($this->whiteHouse->getLongitude(), $array['longitude']);
    }

    public function testCanBeIterated(): void
    {
        $values = $this->whiteHouse->toArray();
        $properties = array_keys($values);

        foreach ($this->whiteHouse->getIterator() as $key => $value) {
            self::assertTrue(in_array($key, $properties));
            self::assertSame($values[$key], $value);
        }
    }
}
