<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use DateTimeImmutable;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CancelMatch;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreatePitch;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateRankingPenalty;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateSeason;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\DeletePitch;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\DeleteRankingPenalty;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\DeleteSeason;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\GenerateMatchDays;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\LocateMatch;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\ScheduleMatch;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\ScheduleMatchesForMatchDay;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\SubmitMatchResult;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\UpdateSeason;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\MatchList;
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
     * @depends testMatchesCanBeScheduledPerMatchDay
     * @param string $seasonId
     * @return string
     */
    public function testTeamCanBeReplacedWhileSeasonInProgress(string $seasonId): string
    {
        $season = $this->getSeason($seasonId);
        self::assertEquals('progress', $season->state);
        $retiringTeamId = $season->ranking->positions[0]->team->id;
        $spareTeamId = self::$spareTeamIds[0];
        self::assertNotEquals($spareTeamId, $retiringTeamId);

        $updatedTeamIds = [];

        foreach (self::$teamIds as $teamId) {
            if ($teamId !== $retiringTeamId) {
                $updatedTeamIds[] = $teamId;
            } else {
                $updatedTeamIds[] = $spareTeamId;
            }
        }

        self::$client->request(new UpdateSeason([
            'id' => $season->id,
            'name' => $season->name,
            'teamIds' => $updatedTeamIds,
            'state' => 'progress'
        ]), $this->defaultAdminAuth);

        $season = $this->getSeason($seasonId);

        $rankingTeamIds = [];
        foreach ($season->ranking->positions as $position) {
            $rankingTeamIds[] = $position->team->id;
        }

        self::assertArraysHaveEqualValues($updatedTeamIds, $rankingTeamIds);

        return $seasonId;
    }

    /**
     * @depends testTeamCanBeReplacedWhileSeasonInProgress
     * @param string $seasonId
     * @return string
     */
    public function testMatchesCanBeQueriedByKickoff(string $seasonId): string
    {
        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);

        $minDate = null;
        $maxDate = null;

        foreach ($season->matchDays as $matchDay) {
            $startDate = new DateTimeImmutable($matchDay->startDate);
            $endDate = new DateTimeImmutable($matchDay->endDate);

            if ($minDate === null || $startDate < $minDate) {
                $minDate = $startDate;
            }

            if ($maxDate === null || $endDate > $maxDate) {
                $maxDate = $endDate;
            }
        }

        $minDate = $minDate->modify('+ 7 days')->setTime(0, 0, 0);
        $maxDate = $maxDate->modify('- 7 days')->setTime(23, 59, 59);

        self::assertTrue($minDate < $maxDate);

        $query = new MatchList([
            'filter' => [
                'kickoffAfter' => self::formatDateTime($minDate),
                'kickoffBefore' => self::formatDateTime($maxDate)
            ]
        ]);

        $matches = [];

        foreach (self::$client->paginate($query) as $matchList) {
            foreach ($matchList as $match) {
                if ($match->kickoff !== null) {
                    $matches[] = $match;
                }
            }
        }

        self::assertNotEmpty($matches);

        foreach ($matches as $match) {
            $kickoff = new DateTimeImmutable($match->kickoff);

            self::assertGreaterThanOrEqual($minDate, $kickoff);
            self::assertLessThanOrEqual($maxDate, $kickoff);
        }

        return $seasonId;
    }

    /**
     * @depends testMatchesCanBeQueriedByKickoff
     * @param string $seasonId
     * @return string
     */
    public function testMatchCanBeLocated(string $seasonId): string
    {
        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);

        $matchId = $season->matchDays[0]->matches[0]->id;

        $pitchId = IdGenerator::generate();
        self::$client->request(new CreatePitch([
            'id' => $pitchId,
            'label' => __METHOD__
        ]), $this->defaultAdminAuth);

        self::$client->request(new LocateMatch([
            'matchId' => $matchId,
            'pitchId' => $pitchId
        ]), $this->defaultAdminAuth);

        $match = $this->getMatch($matchId);
        self::assertIsObject($match);
        self::assertIsObject($match->pitch);
        self::assertSame($pitchId, $match->pitch->id);

        return $matchId;
    }

    /**
     * @depends testTeamCanBeReplacedWhileSeasonInProgress
     * @param string $seasonId
     * @return string
     */
    public function testMatchCanBeScheduled(string $seasonId): string
    {
        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);

        $matchId = $season->matchDays[0]->matches[0]->id;

        $match = $this->getMatch($matchId);
        self::assertIsObject($match);

        $newKickoff = new DateTimeImmutable('next month');
        self::assertNotEquals($newKickoff->getTimestamp(), (new DateTimeImmutable($match->kickoff))->getTimestamp());

        self::$client->request(new ScheduleMatch([
            'matchId' => $matchId,
            'kickoff' => self::formatDateTime($newKickoff)
        ]), $this->defaultAdminAuth);

        $match = $this->getMatch($matchId);
        self::assertIsObject($match);
        self::assertNotNull($match->kickoff);
        self::assertEquals($newKickoff->getTimestamp(), (new DateTimeImmutable($match->kickoff))->getTimestamp());

        return $matchId;
    }

    /**
     * @depends testMatchCanBeScheduled
     * @param string $matchId
     */
    public function testDeletingUsedPitchFails(string $matchId): void
    {
        $match = $this->getMatch($matchId);
        self::assertIsObject($match);
        self::assertNotNull($match->pitch);

        $this->expectClientException();
        self::$client->request(new DeletePitch(['id' => $match->pitch->id]), $this->defaultAdminAuth);
    }

    /**
     * @depends testSeasonCanBeStarted
     * @param string $seasonId
     */
    public function testSubmittingMatchResultByNonParticipatingTeamFails(string $seasonId): void
    {
        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);

        $matchId = $season->matchDays[0]->matches[0]->id;
        $match = $this->getMatch($matchId);
        self::assertIsObject($match);

        $nonParticipatingTeamIds = array_filter(self::$teamIds, function (string $teamId) use ($match) {
            return $teamId !== $match->homeTeam->id && $teamId !== $match->guestTeam->id;
        });

        $teamId = array_shift($nonParticipatingTeamIds);
        self::assertIsString($teamId);

        $teamManagerAuth = self::$teamManagerAuths[$teamId];
        self::assertIsObject($teamManagerAuth);

        $this->expectClientException();
        self::$client->request(new SubmitMatchResult([
            'matchId' => $matchId,
            'matchResult' => [
                'homeScore' => 4,
                'guestScore' => 3
            ]
        ]), $teamManagerAuth);
    }

    /**
     * @depends testSeasonCanBeStarted
     * @param string $seasonId
     * @return string
     */
    public function testSubmittingMatchResultAffectsRanking(string $seasonId): string
    {
        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);

        $matchId = $season->matchDays[0]->matches[0]->id;
        $match = $this->getMatch($matchId);
        self::assertIsObject($match);

        self::$client->request(new SubmitMatchResult([
            'matchId' => $matchId,
            'matchResult' => [
                'homeScore' => 1,
                'guestScore' => 1
            ]
        ]), $this->defaultAdminAuth);

        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);
        self::assertIsObject($season->ranking);

        foreach ($season->ranking->positions as $position) {
            if ($position->team->id === $match->homeTeam->id || $position->team->id === $match->guestTeam->id) {
                self::assertSame(1, $position->number);
                self::assertSame(0, $position->losses);
                self::assertSame(1, $position->draws);
                self::assertSame(0, $position->wins);
                self::assertSame(1, $position->scoredGoals);
                self::assertSame(1, $position->concededGoals);
                self::assertSame(1, $position->points);
            } else {
                self::assertGreaterThan(2, $position->number);
                self::assertSame(0, $position->losses);
                self::assertSame(0, $position->draws);
                self::assertSame(0, $position->wins);
                self::assertSame(0, $position->scoredGoals);
                self::assertSame(0, $position->concededGoals);
                self::assertSame(0, $position->points);
            }
        }

        $now = time();
        $updatedAt = strtotime($season->ranking->updatedAt);
        self::assertLessThan(5, $now - $updatedAt);

        return $seasonId;
    }

    /**
     * @depends testSubmittingMatchResultAffectsRanking
     * @param string $seasonId
     */
    public function testCancellingMatchByNonParticipatingTeamFails(string $seasonId): void
    {
        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);

        $matchId = $season->matchDays[0]->matches[0]->id;
        $match = $this->getMatch($matchId);
        self::assertIsObject($match);

        $nonParticipatingTeamIds = array_filter(self::$teamIds, function (string $teamId) use ($match) {
            return $teamId !== $match->homeTeam->id && $teamId !== $match->guestTeam->id;
        });

        $teamId = array_shift($nonParticipatingTeamIds);
        self::assertIsString($teamId);

        $teamManagerAuth = self::$teamManagerAuths[$teamId];
        self::assertIsObject($teamManagerAuth);

        $this->expectClientException();
        self::$client->request(new CancelMatch([
            'matchId' => $matchId,
            'reason'  => 'Just cause'
        ]), $teamManagerAuth);
    }

    /**
     * @depends testSubmittingMatchResultAffectsRanking
     * @param string $seasonId
     * @return string
     */
    public function testCancellingMatchAffectsRanking(string $seasonId): string
    {
        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);

        $matchId = $season->matchDays[0]->matches[0]->id;
        $match = $this->getMatch($matchId);
        self::assertIsObject($match);


        $teamId = $match->homeTeam->id;
        $teamManagerAuth = self::$teamManagerAuths[$teamId] ?? self::$spareTeamManagerAuths[$teamId];
        self::assertIsObject($teamManagerAuth);

        self::$client->request(new CancelMatch([
            'matchId' => $matchId,
            'reason'  => 'Team did not show up'
        ]), $teamManagerAuth);

        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);

        foreach ($season->ranking->positions as $position) {
            self::assertSame(0, $position->losses);
            self::assertSame(0, $position->draws);
            self::assertSame(0, $position->wins);
            self::assertSame(0, $position->scoredGoals);
            self::assertSame(0, $position->concededGoals);
            self::assertSame(0, $position->points);
        }

        $now = time();
        $updatedAt = strtotime($season->ranking->updatedAt);
        self::assertLessThan(5, $now - $updatedAt);

        return $seasonId;
    }

    /**
     * @depends testCancellingMatchAffectsRanking
     * @depends testCancellingMatchByNonParticipatingTeamFails
     * @param string $seasonId
     * @return string
     */
    public function testPenaltiesAffectRanking(string $seasonId): string
    {
        $penaltyId = IdGenerator::generate();
        $teamId = self::$spareTeamIds[0];

        self::$client->request(new CreateRankingPenalty([
            'id' => $penaltyId,
            'seasonId' => $seasonId,
            'teamId' => $teamId,
            'reason' => 'for not partying hard',
            'points' => 5
        ]), $this->defaultAdminAuth);

        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);

        $positions = array_filter($season->ranking->positions, function ($position) use ($teamId) {
            return $position->team->id === $teamId;
        });
        self::assertSame(1, count($positions));

        $position = array_shift($positions);
        self::assertSame(-5, $position->points);

        $this->markTestIncomplete('DeleteRankingPenalty not implemented yet');

        self::$client->request(new DeleteRankingPenalty([
            'id' => $penaltyId
        ]), $this->defaultAdminAuth);

        $season = $this->getSeason($seasonId);
        self::assertIsObject($season);

        $positions = array_filter($season->ranking->positions, function ($position) use ($teamId) {
            return $position->team->id === $teamId;
        });
        self::assertSame(1, count($positions));

        $position = array_shift($positions);
        self::assertSame(0, $position->points);

        return $seasonId;
    }

    /**
     * @dataProvider filterProvider
     */
    public function testSeasonsCanBeListed(array $filter): void
    {
        $seasonList = self::$client->request(new SeasonList(['filter' => $filter]));

        self::assertIsArray($seasonList);
        self::assertNotEmpty($seasonList);
        foreach ($seasonList as $season) {
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
        return self::$client->request(new Season(['id' => $id]));
    }

    private function getMatch(string $id): ?object
    {
        return self::$client->request(new MatchQuery(['id' => $id]));
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
