<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Domain\Value\GeographicLocation;
use HexagonalPlayground\Tests\Framework\DataGenerator;
use Iterator;
use PHPUnit\Framework\TestCase;

class ValueObjectTest extends TestCase
{
    private array $locations;

    protected function setUp(): void
    {
        $this->locations = iterator_to_array($this->generateLocations(2));
    }

    public function testCanBeCheckedForEquality(): void
    {
        self::assertTrue($this->locations[0]->equals(clone $this->locations[0]));
        self::assertFalse($this->locations[0]->equals($this->locations[1]));
        self::assertFalse($this->locations[1]->equals($this->locations[0]));
    }

    public function testCanBeConvertedToArray(): void
    {
        $array = $this->locations[1]->toArray();

        self::assertSame($this->locations[1]->getLatitude(), $array['latitude']);
        self::assertSame($this->locations[1]->getLongitude(), $array['longitude']);
    }

    public function testCanBeIterated(): void
    {
        $values = $this->locations[1]->toArray();
        $properties = array_keys($values);

        foreach ($this->locations[1]->getIterator() as $key => $value) {
            self::assertTrue(in_array($key, $properties));
            self::assertSame($values[$key], $value);
        }
    }

    private function generateLocations(int $count): Iterator
    {
        for ($i = 0; $i < $count; $i++) {
            yield new GeographicLocation(DataGenerator::generateLongitude(), DataGenerator::generateLatitude());
        }
    }
}
