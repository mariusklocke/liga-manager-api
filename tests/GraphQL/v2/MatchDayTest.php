<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use DateTime;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\MatchDayList;
use Iterator;

class MatchDayTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     */
    public function testMatchDaysCanBeListed(array $filter): void
    {
        $query = new MatchDayList([
            'filter' => $filter
        ]);

        foreach (self::$client->paginate($query) as $matchDayList) {
            foreach ($matchDayList as $matchDay) {
                self::assertObjectHasProperty('id', $matchDay);
                self::assertObjectHasProperty('number', $matchDay);
                self::assertObjectHasProperty('startDate', $matchDay);
                self::assertObjectHasProperty('endDate', $matchDay);
            }
        }
    }

    public static function filterProvider(): Iterator
    {
        yield 'empty filter' => [[]];
        yield 'simple filter' => [[
            'startsAfter' => self::formatDateTime(new DateTime('1980-01-01')),
            'endsBefore' => self::formatDate(new DateTime('2099-01-01')),
        ]];
        yield 'complete filter' => [[
            'startsAfter' => self::formatDateTime(new DateTime('1980-01-01')),
            'startsBefore' => self::formatDate(new DateTime('2099-01-01')),
            'endsAfter' => self::formatDateTime(new DateTime('1980-01-01')),
            'endsBefore' => self::formatDate(new DateTime('2099-01-01')),
        ]];
    }
}
