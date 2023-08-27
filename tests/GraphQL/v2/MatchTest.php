<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use DateTime;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\MatchList;
use Iterator;

class MatchTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     */
    public function testMatchesCanBeListed(array $filter): void
    {
        $query = new MatchList([
            'filter' => $filter
        ]);

        foreach (self::$client->paginate($query) as $matchList) {
            foreach ($matchList as $match) {
                self::assertObjectHasProperty('id', $match);
                self::assertObjectHasProperty('homeTeam', $match);
                self::assertObjectHasProperty('guestTeam', $match);
                self::assertObjectHasProperty('matchDay', $match);
                self::assertObjectHasProperty('id', $match->matchDay);

                if (isset($match->kickoff)) {
                    self::assertIsString($match->kickoff);
                }

                if (isset($match->pitch)) {
                    self::assertObjectHasProperty('id', $match->pitch);
                    self::assertObjectHasProperty('label', $match->pitch);
                }

                if (isset($match->result)) {
                    self::assertObjectHasProperty('homeScore', $match->result);
                    self::assertObjectHasProperty('guestScore', $match->result);
                }

                if (isset($match->cancellation)) {
                    self::assertObjectHasProperty('createdAt', $match->cancellation);
                    self::assertObjectHasProperty('reason', $match->cancellation);
                }
            }
        }
    }

    public static function filterProvider(): Iterator
    {
        yield 'empty filter' => [[]];
        yield 'simple filter' => [[
            'kickoffAfter' => self::formatDateTime(new DateTime('1980-01-01'))
        ]];
        yield 'complete filter' => [[
            'kickoffAfter' => self::formatDateTime(new DateTime('1980-01-01')),
            'kickoffBefore' => self::formatDateTime(new DateTime('2099-01-01'))
        ]];
    }
}
