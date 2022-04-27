<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use DateTime;
use Iterator;

class MatchTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     */
    public function testMatchesCanBeListed(array $filter): void
    {
        $query = self::$client->createQuery('matchList')
            ->fields([
                'id',
                'matchDay' => [
                    'id'
                ],
                'homeTeam' => [
                    'id',
                    'name'
                ],
                'guestTeam' => [
                    'id',
                    'name'
                ],
                'kickoff',
                'pitch' => [
                    'id',
                    'label'
                ],
                'result' => [
                    'homeScore',
                    'guestScore'
                ],
                'cancellation' => [
                    'createdAt',
                    'reason'
                ]
            ])
            ->argTypes([
                'filter' => 'MatchFilter'
            ])
            ->argValues([
                'filter' => $filter
            ]);

        $response = self::$client->request($query);

        self::assertResponseNotHasError($response);
        self::assertObjectHasAttribute('data', $response);
        self::assertObjectHasAttribute('matchList', $response->data);
        self::assertIsArray($response->data->matchList);
        self::assertNotEmpty($response->data->matchList);

        foreach ($response->data->matchList as $match) {
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

    public function filterProvider(): Iterator
    {
        yield 'empty filter' => [[]];
        yield 'simple filter' => [[
            'kickoffAfter' => self::formatDate(new DateTime('1980-01-01'))
        ]];
        yield 'complete filter' => [[
            'kickoffAfter' => self::formatDate(new DateTime('1980-01-01')),
            'kickoffBefore' => self::formatDate(new DateTime('2099-01-01'))
        ]];
    }
}
