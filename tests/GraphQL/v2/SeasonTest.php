<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use DateTimeImmutable;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateSeason;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\DeleteSeason;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\GenerateMatchDays;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\ScheduleMatchesForMatchDay;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\UpdateSeason;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\MatchQuery;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\Season;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\SeasonList;
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

    public function testMatchDaysCanBeGenerated(): string
    {
        $matchDayDates = iterator_to_array($this->generateMatchDayDates(count(self::$teamIds) - 1));
        $seasonId = IdGenerator::generate();

        self::$client->request(new CreateSeason([
            'id' => $seasonId,
            'name' => __METHOD__,
            'teamIds' => self::$teamIds
        ]), $this->defaultAdminAuth);

        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);
        self::assertCount(0, $season->matchDays);

        self::$client->request(new GenerateMatchDays([
            'seasonId' => $seasonId,
            'matchDayDates' => $matchDayDates
        ]), $this->defaultAdminAuth);

        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);
        self::assertCount(count($matchDayDates), $season->matchDays);

        foreach ($season->matchDays as $matchDay) {
            self::assertIsArray($matchDay->matches);
            self::assertCount(count($season->teams) / 2, $matchDay->matches);
        }

        return $seasonId;
    }

    /**
     * @depends testMatchDaysCanBeGenerated
     * @param string $seasonId
     * @return string
     */
    public function testSeasonCanBeStarted(string $seasonId): string
    {
        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);
        self::assertNotEmpty($season->matchDays);
        self::assertNotEmpty($season->teams);

        self::$client->request(new UpdateSeason([
            'id' => $season->id,
            'name' => $season->name,
            'teamIds' => self::$teamIds,
            'state' => 'progress'
        ]), $this->defaultAdminAuth);

        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);
        self::assertNotNull($season->ranking);
        self::assertEquals('progress', $season->state);

        return $seasonId;
    }

    /**
     * @depends testSeasonCanBeStarted
     * @param string $seasonId
     * @return string
     */
    public function testMatchesCanBeScheduledPerMatchDay(string $seasonId): string
    {
        $appointments = $this->createMatchAppointments();

        $season = $this->getSeason($seasonId);
        foreach ($season->matchDays as $matchDay) {
            self::$client->request(new ScheduleMatchesForMatchDay([
                'matchDayId' => $matchDay->id,
                'matchAppointments' => $appointments
            ]), $this->defaultAdminAuth);

            foreach ($matchDay->matches as $match) {
                $match = $this->getMatch($match->id);

                self::assertNotNull($match);
                self::assertNotNull($match->pitch, 'Invalid match: ' . print_r($match, true));
                self::assertNotNull($match->kickoff, 'Invalid match: ' . print_r($match, true));
            }
        }

        return $seasonId;
    }

    /**
     * @dataProvider filterProvider
     */
    public function testSeasonsCanBeListed(array $filter): void
    {
        $response = self::$client->request(new SeasonList(['filter' => $filter]));

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
        $response = self::$client->request(new Season(['id' => $id]));

        if (isset($response->data) && isset($response->data->season)) {
            return $response->data->season;
        }

        return null;
    }

    private function getMatch(string $id): ?object
    {
        $response = self::$client->request(new MatchQuery(['id' => $id]));

        if (isset($response->data) && isset($response->data->match)) {
            return $response->data->match;
        }

        return null;
    }

    private function getTeamIdsFromSeason(object $season): array
    {
        return array_map(function (object $team) {
            return $team->id;
        }, $season->teams);
    }

    private function generateMatchDayDates(int $count): Iterator
    {
        $start  = new DateTimeImmutable('next saturday');
        $end    = $start->modify('+1 day');
        for ($i = 0; $i < $count; $i++) {
            yield [
                'from' => self::formatDate($start),
                'to'   => self::formatDate($end)
            ];
            $start = $start->modify('+7 days');
            $end   = $end->modify('+7 days');
        }
    }

    private function createMatchAppointments(): array
    {
        $appointments = [];
        $saturday = new DateTimeImmutable('next saturday');
        $sunday = $saturday->modify('+1 day');

        $appointments[] = [
            'kickoff' => self::formatDateTime($saturday->setTime(15, 30)),
            'unavailableTeamIds' => [],
            'pitchId' => self::$pitchIds[0]
        ];

        $appointments[] = [
            'kickoff' => self::formatDateTime($saturday->setTime(17, 30)),
            'unavailableTeamIds' => [self::$teamIds[0], self::$teamIds[1]],
            'pitchId' => self::$pitchIds[1]
        ];

        $appointments[] = [
            'kickoff' => self::formatDateTime($sunday->setTime(12, 00)),
            'unavailableTeamIds' => [self::$teamIds[2]],
            'pitchId' => self::$pitchIds[0]
        ];

        $appointments[] = [
            'kickoff' => self::formatDateTime($sunday->setTime(14, 00)),
            'unavailableTeamIds' => [],
            'pitchId' => self::$pitchIds[1]
        ];

        return $appointments;
    }
}
