<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

use HexagonalPlayground\Tests\Functional\Framework\ApiException;

class BasicUseCaseTest extends TestCase
{
    public function testPitchCanBeCreated(): array
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $pitchIds = [];
        $response = $this->client->createPitch('TestFloat', 89.99, 6.78);
        self::assertResponseHasValidId($response);
        $pitchIds[] = $response->id;
        $response = $this->client->createPitch('TestInt', 89, 6);
        self::assertResponseHasValidId($response);
        $pitchIds[] = $response->id;

        return $pitchIds;
    }

    public function testSeasonCanBeCreated(): string
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $response = $this->client->createSeason('bar');
        self::assertResponseHasValidId($response);
        return $response->id;
    }

    public function testSeasonCanBeDeleted()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $response = $this->client->createSeason('foo');
        self::assertObjectHasAttribute('id', $response);
        self::assertGreaterThan(0, strlen($response->id));
        $seasonList = $this->client->getAllSeasons();

        $this->client->deleteSeason($response->id);
        self::assertEquals(count($seasonList) - 1, count($this->client->getAllSeasons()));
    }

    public function testTeamsCanBeCreated(): array
    {
        $teamIds = [];
        $this->client->setBasicAuth('admin@example.com', '123456');
        for ($i = 1; $i <= 4; $i++) {
            $response = $this->client->createTeam('Team No. ' . $i);
            self::assertObjectHasAttribute('id', $response);
            $teamIds[] = $response->id;
        }

        return $teamIds;
    }

    /**
     * @param string $seasonId
     *
     * @depends testSeasonCanBeCreated
     */
    public function testSeasonCanBeFound(string $seasonId)
    {
        $seasonList = $this->client->getAllSeasons();
        self::assertIsArray($seasonList);

        $found = false;
        foreach ($seasonList as $season) {
            self::assertObjectHasAttribute('id', $season);
            self::assertObjectHasAttribute('name', $season);
            self::assertObjectHasAttribute('state', $season);
            if (!$found && $season->id === $seasonId) {
                $found = true;
            }
        }
        self::assertTrue($found);

        $season = $this->client->getSeason($seasonId);
        self::assertObjectHasAttribute('id', $season);
        self::assertObjectHasAttribute('name', $season);
        self::assertObjectHasAttribute('state', $season);
    }

    /**
     * @param array $teamIds
     * @param string $seasonId
     * @return string
     *
     * @depends testTeamsCanBeCreated
     * @depends testSeasonCanBeCreated
     */
    public function testTeamsCanBeAddedToSeason(array $teamIds, string $seasonId) : string
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $previousTeams = $this->client->getTeamsInSeason($seasonId);
        foreach ($teamIds as $teamId) {
            $this->client->addTeamToSeason($seasonId, $teamId);
        }
        self::assertEquals(count($previousTeams) + count($teamIds), count($this->client->getTeamsInSeason($seasonId)));

        return $seasonId;
    }

    /**
     * @param string $seasonId
     * @return string
     * @depends testTeamsCanBeAddedToSeason
     */
    public function testMatchesCanBeCreated(string $seasonId) : string
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $this->client->createMatches($seasonId, self::createMatchDaysDates(3));
        $season = $this->client->getSeason($seasonId);
        self::assertObjectHasAttribute('match_day_count', $season);
        self::assertGreaterThan(0, $season->match_day_count);

        return $seasonId;
    }

    /**
     * @param string $seasonId
     * @return array
     * @depends testMatchesCanBeCreated
     */
    public function testMatchesCanBeFound(string $seasonId) : array
    {
        $matches = $this->client->getMatchesBySeasonId($seasonId);
        self::assertEquals(6, count($matches));
        $matchIds = [];
        foreach ($matches as $match) {
            self::assertObjectHasAttribute('id', $match);
            $matchIds[] = $match->id;
        }

        return $matchIds;
    }

    /**
     * @param string $seasonId
     * @return string
     * @depends testMatchesCanBeCreated
     */
    public function testSeasonCanBeStarted(string $seasonId) : string
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $this->client->startSeason($seasonId);
        $season = $this->client->getSeason($seasonId);
        self::assertObjectHasAttribute('state', $season);
        self::assertEquals('progress', $season->state);
        return $seasonId;
    }

    /**
     * @param string $seasonId
     * @return string
     * @depends testSeasonCanBeStarted
     */
    public function testRankingCanBeFound(string $seasonId)
    {
        $ranking = $this->client->getSeasonRanking($seasonId);

        self::assertObjectHasAttribute('positions', $ranking);
        self::assertObjectHasAttribute('updated_at', $ranking);
        $positions = $ranking->positions;
        self::assertIsArray($positions);
        $count = 0;
        $expectedProperties = [
            'conceded_goals',
            'draws',
            'losses',
            'matches',
            'number',
            'points',
            'scored_goals',
            'season_id',
            'sort_index',
            'team_id',
            'wins'
        ];
        foreach ($positions as $position) {
            foreach ($expectedProperties as $expectedProperty) {
                self::assertObjectHasAttribute($expectedProperty, $position);
            }
            $count++;
        }
        self::assertEquals(4, $count);

        self::assertObjectHasAttribute('penalties', $ranking);
        self::assertIsArray($ranking->penalties);
        self::assertCount(0, $ranking->penalties);

        return $seasonId;
    }

    /**
     * @param string[] $matchIds
     * @depends testMatchesCanBeFound
     * @depends testRankingCanBeFound
     */
    public function testSubmittingMatchResultsRequiresPermission(array $matchIds)
    {
        self::expectException(ApiException::class);
        self::expectExceptionCode(403);
        $matchId = array_shift($matchIds);
        $this->client->setBasicAuth('user1@example.com', '123456');
        $this->client->submitMatchResult($matchId, 2, 2);
        $this->client->clearAuth();
    }

    /**
     * @param string[] $matchIds
     * @param string   $seasonId
     * @return string
     * @depends testMatchesCanBeFound
     * @depends testRankingCanBeFound
     */
    public function testMatchResultCanBeSubmitted(array $matchIds, string $seasonId) : string
    {
        $matchId = array_shift($matchIds);
        $this->client->setBasicAuth('admin@example.com', '123456');
        $this->client->submitMatchResult($matchId, 3, 1);
        $this->client->clearAuth();
        $match = $this->client->getMatch($matchId);
        self::assertObjectHasAttribute('home_score', $match);
        self::assertObjectHasAttribute('guest_score', $match);
        self::assertObjectHasAttribute('home_team_id', $match);
        self::assertObjectHasAttribute('guest_team_id', $match);
        self::assertEquals(3, $match->home_score);
        self::assertEquals(1, $match->guest_score);

        $ranking = $this->client->getSeasonRanking($seasonId);
        self::assertObjectHasAttribute('positions', $ranking);
        $positions = $ranking->positions;
        self::assertIsArray($positions);

        $count = 0;
        $found = false;
        foreach ($positions as $position) {
            if (!$found && $position->wins === 1) {
                $found = $position->scored_goals === 3 && $position->conceded_goals === 1;
            }
            $count++;
        }
        self::assertTrue($found);
        self::assertEquals(4, $count);

        return $matchId;
    }

    /**
     * @param string $seasonId
     * @param string $matchId
     * @return string
     * @depends testRankingCanBeFound
     * @depends testMatchResultCanBeSubmitted
     */
    public function testPenaltiesAffectRanking(string $seasonId, string $matchId)
    {
        $this->client->setBasicAuth('admin@example.com', '123456');

        $ranking = $this->client->getSeasonRanking($seasonId);
        $topRank = $ranking->positions[0];
        $leaderTeamId = $topRank->team_id;
        $previousPoints = $topRank->points;
        self::assertGreaterThan(0, $previousPoints);

        $penalty = $this->client->addRankingPenalty($seasonId, $leaderTeamId, 'For testing', 5);

        $ranking = $this->client->getSeasonRanking($seasonId);
        $topRank = $ranking->positions[0];
        self::assertCount(1, $ranking->penalties);
        self::assertNotEquals($leaderTeamId, $topRank->team_id);

        $this->client->removeRankingPenalty($seasonId, $penalty->id);
        $ranking = $this->client->getSeasonRanking($seasonId);
        $topRank = $ranking->positions[0];
        self::assertEquals($leaderTeamId, $topRank->team_id);

        return $matchId;
    }

    /**
     * @param string $matchId
     * @depends testPenaltiesAffectRanking
     */
    public function testMatchCanBeCancelled(string $matchId)
    {
        $this->client->setBasicAuth('admin@example.com', '123456');

        $match = $this->client->getMatch($matchId);
        self::assertObjectHasAttribute('cancelled_at', $match);
        self::assertNull($match->cancelled_at);
        self::assertObjectHasAttribute('cancellation_reason', $match);
        self::assertNull($match->cancellation_reason);
        self::assertNotNull($match->home_score);
        self::assertNotNull($match->guest_score);

        $reason = 'Team did not show up';
        $this->client->cancelMatch($matchId, $reason);
        $match = $this->client->getMatch($matchId);

        self::assertObjectHasAttribute('cancelled_at', $match);
        $cancelledAt = new \DateTimeImmutable($match->cancelled_at);
        self::assertLessThan(5, time() - $cancelledAt->getTimestamp());
        self::assertObjectHasAttribute('cancellation_reason', $match);
        self::assertEquals($reason, $match->cancellation_reason);
        self::assertNull($match->home_score);
        self::assertNull($match->guest_score);
    }

    /**
     * @param string $matchId
     * @param string $seasonId
     * @depends testMatchResultCanBeSubmitted
     * @depends testRankingCanBeFound
     */
    public function testEndedSeasonsRankingCannotBeChanged(string $matchId, string $seasonId): void
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $this->client->endSeason($seasonId);

        try {
            $this->client->submitMatchResult($matchId, 4, 3);
        } catch (ApiException $exception) {

        }

        self::assertInstanceOf(ApiException::class, $exception);
        self::assertEquals(400, $exception->getCode());
    }

    /**
     * @param string[] $matchIds
     * @param string[] $pitchIds
     * @depends testMatchesCanBeFound
     * @depends testPitchCanBeCreated
     */
    public function testMatchCanBeLocated(array $matchIds, array $pitchIds)
    {
        $matchId = array_shift($matchIds);
        $pitchId = array_shift($pitchIds);
        $this->client->setBasicAuth('admin@example.com', '123456');
        $this->client->locateMatch($matchId, $pitchId);

        $match = $this->client->getMatch($matchId);
        self::assertEquals($pitchId, $match->pitch_id);
    }

    /**
     * @param string[] $matchIds
     * @depends testMatchesCanBeFound
     */
    public function testMatchCanBeScheduled(array $matchIds)
    {
        $matchId = array_shift($matchIds);
        $this->client->setBasicAuth('admin@example.com', '123456');
        $this->client->scheduleMatch($matchId, '2018-10-06');

        $match = $this->client->getMatch($matchId);
        self::assertEquals('2018-10-06T00:00:00Z', $match->kickoff);
    }

    /**
     * @param string[] $teamIds
     * @depends testTeamsCanBeCreated
     */
    public function testTeamContactCanBeUpdated(array $teamIds)
    {
        $teamId = array_shift($teamIds);
        $contact = [
            'first_name' => 'Homer',
            'last_name'  => 'Simpson',
            'phone'      => '012345',
            'email'      => 'homer.simpson@example.com'
        ];
        $this->client->setBasicAuth('admin@example.com', '123456');
        $this->client->updateTeamContact($teamId, $contact);

        $team = $this->client->getTeam($teamId);
        self::assertEquals($contact, (array)$team->contact);
    }

    /**
     * @param string[] $pitchIds
     * @depends testPitchCanBeCreated
     */
    public function testPitchContactCanBeUpdated(array $pitchIds)
    {
        $pitchId = array_shift($pitchIds);
        $contact = [
            'first_name' => 'Lisa',
            'last_name'  => 'Simpson',
            'phone'      => '012345',
            'email'      => 'lisa.simpson@example.com'
        ];
        $this->client->setBasicAuth('admin@example.com', '123456');
        $this->client->updatePitchContact($pitchId, $contact);

        $pitch = $this->client->getPitch($pitchId);
        self::assertEquals($contact, (array)$pitch->contact);
    }

    /**
     * @return string
     */
    public function testTournamentCanBeCreated(): string
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $response = $this->client->createTournament('Foo');
        self::assertObjectHasAttribute('id', $response);
        self::assertIsString($response->id);
        self::assertGreaterThan(0, strlen($response->id));

        return $response->id;
    }

    /**
     * @param string $tournamentId
     * @param array $teamIds
     * @depends testTournamentCanBeCreated
     * @depends testTeamsCanBeCreated
     * @return string
     */
    public function testTournamentRoundsCanBeCreated(string $tournamentId, array $teamIds)
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $firstRound = [
            ['home_team_id' => $teamIds[0], 'guest_team_id' => $teamIds[1]],
            ['home_team_id' => $teamIds[2], 'guest_team_id' => $teamIds[3]]
        ];
        $datePeriod = ['from' => '2018-03-01', 'to' => '2018-03-02'];
        $this->client->setTournamentRound($tournamentId, 1, $firstRound, $datePeriod);
        $tournament = $this->client->getTournament($tournamentId);
        self::assertObjectHasAttribute('rounds', $tournament);
        self::assertEquals(1, $tournament->rounds);
        $matches = $this->client->getMatchesInTournament($tournamentId);
        self::assertEquals(2, count($matches));

        $secondRound = [
            ['home_team_id' => $teamIds[1], 'guest_team_id' => $teamIds[2]]
        ];
        $this->client->setTournamentRound($tournamentId, 2, $secondRound, $datePeriod);
        $tournament = $this->client->getTournament($tournamentId);
        self::assertObjectHasAttribute('rounds', $tournament);
        self::assertEquals(2, $tournament->rounds);
        $matches = $this->client->getMatchesInTournament($tournamentId);
        self::assertEquals(3, count($matches));

        return $tournamentId;
    }

    /**
     * @param string $seasonId
     * @depends testMatchesCanBeCreated
     */
    public function testMatchDayCanBeRescheduled(string $seasonId)
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $matchDays = $this->client->getMatchDaysForSeason($seasonId);
        self::assertGreaterThan(0, count($matchDays));
        $matchDay = array_shift($matchDays);
        self::assertResponseHasValidId($matchDay);
        $matchDayId = $matchDay->id;
        $this->client->rescheduleMatchDay($matchDay->id, ['from' => '2018-10-05', 'to' => '2018-10-07']);

        $matchDays = array_filter($this->client->getMatchDaysForSeason($seasonId), function ($matchDay) use ($matchDayId) {
            return $matchDay->id === $matchDayId;
        });
        self::assertEquals(1, count($matchDays));
        $matchDay = array_shift($matchDays);

        self::assertEquals('2018-10-05', $matchDay->start_date);
        self::assertEquals('2018-10-07', $matchDay->end_date);
    }

    /**
     * @param string $tournamentId
     * @depends testTournamentRoundsCanBeCreated
     */
    public function testTournamentCanBeDeleted(string $tournamentId)
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $matches = $this->client->getMatchesInTournament($tournamentId);
        self::assertNotEmpty($matches);

        $this->client->deleteTournament($tournamentId);

        $exception = null;
        try {
            $this->client->getTournament($tournamentId);
        } catch (ApiException $e) {
            $exception = $e;
        }

        self::assertInstanceOf(ApiException::class, $exception);
        self::assertEquals(404, $exception->getCode());

        foreach ($matches as $match) {
            $exception = null;
            try {
                $this->client->getMatch($match->id);
            } catch (ApiException $e) {
                $exception = $e;
            }

            self::assertInstanceOf(ApiException::class, $exception);
            self::assertEquals(404, $exception->getCode());
        }
    }

    private static function createMatchDaysDates(int $count): array
    {
        $result = [];
        $start  = new \DateTime('2018-09-29');
        $end    = new \DateTime('2018-09-30');
        for ($i = 0; $i < $count; $i++) {
            $result[] = [
                'from' => $start->format('Y-m-d'),
                'to'   => $end->format('Y-m-d')
            ];
            $start->modify('+7 days');
            $end->modify('+7 days');
        }

        return $result;
    }
}