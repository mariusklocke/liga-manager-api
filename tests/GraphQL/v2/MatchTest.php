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
                self::assertObjectHasAttribute('id', $match);
                self::assertObjectHasAttribute('homeTeam', $match);
                self::assertObjectHasAttribute('guestTeam', $match);
                self::assertObjectHasAttribute('matchDay', $match);
                self::assertObjectHasAttribute('id', $match->matchDay);

                if (isset($match->kickoff)) {
                    self::assertIsString($match->kickoff);
                }

                if (isset($match->pitch)) {
                    self::assertObjectHasAttribute('id', $match->pitch);
                    self::assertObjectHasAttribute('label', $match->pitch);
                }

                if (isset($match->result)) {
                    self::assertObjectHasAttribute('homeScore', $match->result);
                    self::assertObjectHasAttribute('guestScore', $match->result);
                }

                if (isset($match->cancellation)) {
                    self::assertObjectHasAttribute('createdAt', $match->cancellation);
                    self::assertObjectHasAttribute('reason', $match->cancellation);
                }
            }
        }
    }

    public function filterProvider(): Iterator
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