<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateSeason;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\DeleteSeason;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\UpdateSeason;
use HexagonalPlayground\Tests\Framework\IdGenerator;
use Iterator;

class SeasonTest extends CompetitionTest
{
    public function testSeasonCanBeCreated(): string
    {
        $id = IdGenerator::generate();
        $name = __METHOD__;

        self::$client->request(new CreateSeason([
            'id' => $id,
            'name' => $name,
            'teamIds' => self::$teamIds
        ]), $this->defaultAdminAuth);

        $season = $this->getSeason($id);
        self::assertIsObject($season);
        self::assertEquals($id, $season->id);
        self::assertEquals($name, $season->name);
        self::assertArraysHaveEqualValues(self::$teamIds, $this->getTeamIdsFromSeason($season));

        return $id;
    }

    /**
     * @depends testSeasonCanBeCreated
     * @param string $id
     */
    public function testSeasonCanBeDeleted(string $id): void
    {
        self::assertNotNull($this->getSeason($id));

        self::$client->request(new DeleteSeason([
            'id' => $id
        ]), $this->defaultAdminAuth);

        self::assertNull($this->getSeason($id));
    }

    public function testTeamsInSeasonCanBeChanged(): void
    {
        $id = IdGenerator::generate();
        $name = __METHOD__;

        self::$client->request(new CreateSeason([
            'id' => $id,
            'name' => $name,
            'teamIds' => []
        ]), $this->defaultAdminAuth);

        $season = $this->getSeason($id);
        self::assertIsObject($season);
        self::assertCount(0, $season->teams);

        self::$client->request(new UpdateSeason([
            'id' => $id,
            'name' => $name,
            'teamIds' => self::$teamIds,
            'state' => 'preparation'
        ]), $this->defaultAdminAuth);

        $season = $this->getSeason($id);
        self::assertIsObject($season);
        self::assertArraysHaveEqualValues(self::$teamIds, $this->getTeamIdsFromSeason($season));

        self::$client->request(new UpdateSeason([
            'id' => $id,
            'name' => $name,
            'teamIds' => self::$spareTeamIds,
            'state' => 'preparation'
        ]), $this->defaultAdminAuth);

        $season = $this->getSeason($id);
        self::assertIsObject($season);
        self::assertArraysHaveEqualValues(self::$spareTeamIds, $this->getTeamIdsFromSeason($season));

        self::$client->request(new UpdateSeason([
            'id' => $id,
            'name' => $name,
            'teamIds' => [],
            'state' => 'preparation'
        ]), $this->defaultAdminAuth);

        $season = $this->getSeason($id);
        self::assertIsObject($season);
        self::assertCount(0, $season->teams);
    }

    /**
     * @dataProvider filterProvider
     */
    public function testSeasonsCanBeListed(array $filter): void
    {
        $query = self::$client->createQuery('seasonList')
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

        $response = self::$client->request($query);

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

    private function getSeason(string $id): ?object
    {
        $query = self::$client->createQuery('season')
            ->fields([
                'id',
                'name',
                'teams' => [
                    'id',
                    'name'
                ]
            ])
            ->argTypes(['id' => 'String!'])
            ->argValues(['id' => $id]);

        $response = self::$client->request($query);

        if (isset($response->data) && isset($response->data->season)) {
            return $response->data->season;
        }

        return null;
    }

    private function getTeamIdsFromSeason(object $season): array
    {
        return array_map(function (object $team) {
            return $team->id;
        }, $season->teams);
    }
}
