<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use Iterator;

class SeasonTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     */
    public function testSeasonsCanBeListed(array $filter): void
    {
        $query = $this->createQuery('seasonList')
            ->fields([
                'id',
                'name',
                'state',
                'matchDayCount',
                'teamCount',
                'matchDays' => [
                    'id',
                    'number',
                    'matches' => [
                        'id'
                    ]
                ],
                'teams' => [
                    'id',
                    'name'
                ],
                'ranking' => [
                    'updatedAt',
                    'positions' => [
                        'team' => [
                            'id'
                        ],
                        'sortIndex',
                        'number',
                        'matches',
                        'wins',
                        'draws',
                        'losses',
                        'scoredGoals',
                        'concededGoals',
                        'points'
                    ],
                    'penalties' => [
                        'id',
                        'team' => [
                            'id'
                        ],
                        'reason',
                        'createdAt',
                        'points'
                    ]
                ]
            ])
            ->argTypes([
                'filter' => 'SeasonFilter'
            ])
            ->argValues([
                'filter' => $filter
            ]);

        $response = $this->request($query);

        self::assertResponseNotHasError($response);
        self::assertObjectHasAttribute('data', $response);
        self::assertObjectHasAttribute('seasonList', $response->data);
        self::assertIsArray($response->data->seasonList);
        self::assertNotEmpty($response->data->seasonList);

        foreach ($response->data->seasonList as $season) {
            self::assertObjectHasAttribute('id', $season);
            self::assertObjectHasAttribute('name', $season);
            self::assertObjectHasAttribute('state', $season);
            self::assertObjectHasAttribute('matchDayCount', $season);
            self::assertObjectHasAttribute('teamCount', $season);

            self::assertIsArray($season->matchDays);
            foreach ($season->matchDays as $matchDay) {
                self::assertObjectHasAttribute('id', $matchDay);
                self::assertObjectHasAttribute('number', $matchDay);
                self::assertIsArray($matchDay->matches);
                foreach ($matchDay->matches as $match) {
                    self::assertObjectHasAttribute('id', $match);
                }
            }

            self::assertIsArray($season->teams);
            foreach ($season->teams as $team) {
                self::assertObjectHasAttribute('id', $team);
                self::assertObjectHasAttribute('name', $team);
            }

            if ($season->state !== 'preparation') {
                self::assertObjectHasAttribute('ranking', $season);
                self::assertObjectHasAttribute('updatedAt', $season->ranking);
                self::assertObjectHasAttribute('positions', $season->ranking);

                self::assertIsArray($season->ranking->positions);
                foreach ($season->ranking->positions as $position) {
                    self::assertObjectHasAttribute('team', $position);
                    self::assertObjectHasAttribute('sortIndex', $position);
                    self::assertObjectHasAttribute('number', $position);
                    self::assertObjectHasAttribute('matches', $position);
                    self::assertObjectHasAttribute('wins', $position);
                    self::assertObjectHasAttribute('draws', $position);
                    self::assertObjectHasAttribute('losses', $position);
                    self::assertObjectHasAttribute('scoredGoals', $position);
                    self::assertObjectHasAttribute('concededGoals', $position);
                    self::assertObjectHasAttribute('points', $position);
                }

                self::assertIsArray($season->ranking->penalties);
                foreach ($season->ranking->penalties as $penalty) {
                    self::assertObjectHasAttribute('id', $penalty);
                    self::assertObjectHasAttribute('team', $penalty);
                    self::assertObjectHasAttribute('reason', $penalty);
                    self::assertObjectHasAttribute('createdAt', $penalty);
                    self::assertObjectHasAttribute('points', $penalty);
                }
            }
        }
    }

    public function filterProvider(): Iterator
    {
        yield 'empty filter' => [[]];
        yield 'simple filter' => [[
            'states' => ['preparation']
        ]];
        yield 'complete filter' => [[
            'states' => ['preparation', 'progress', 'ended']
        ]];
    }
}
