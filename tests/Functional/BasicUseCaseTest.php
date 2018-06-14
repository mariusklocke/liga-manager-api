<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

use HexagonalPlayground\Tests\Functional\Framework\ApiException;

class BasicUseCaseTest extends TestCase
{
    public function testSeasonCanBeCreated(): string
    {
        $this->client->setBasicAuth('admin', '123456');
        $response = $this->client->createSeason('bar');
        self::assertObjectHasAttribute('id', $response);
        self::assertGreaterThan(0, strlen($response->id));
        return $response->id;
    }

    public function testSeasonCanBeDeleted()
    {
        $this->client->setBasicAuth('admin', '123456');
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
        $this->client->setBasicAuth('admin', '123456');
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
        self::assertInternalType('array', $seasonList);

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
        $this->client->setBasicAuth('admin', '123456');
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
        $this->client->setBasicAuth('admin', '123456');
        $this->client->createMatches($seasonId, '2018-03-02');
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
        $matches = $this->client->findMatchesByMatchDay($seasonId, 1);
        self::assertEquals(2, count($matches));
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
        $this->client->setBasicAuth('admin', '123456');
        $this->client->startSeason($seasonId);
        $season = $this->client->getSeason($seasonId);
        self::assertObjectHasAttribute('state', $season);
        self::assertEquals('progress', $season->state);
        return $seasonId;
    }

    /**
     * @param string $seasonId
     *
     * @depends testSeasonCanBeStarted
     */
    public function testRankingCanBeFound(string $seasonId)
    {
        $ranking = $this->client->getSeasonRanking($seasonId);

        self::assertObjectHasAttribute('positions', $ranking);
        self::assertObjectHasAttribute('updated_at', $ranking);
        $positions = $ranking->positions;
        self::assertInternalType('array', $positions);
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
        $this->client->setBasicAuth('user1', '123456');
        $this->client->submitMatchResult($matchId, 2, 2);
        $this->client->clearAuth();
    }

    /**
     * @param string[] $matchIds
     * @return string
     * @depends testMatchesCanBeFound
     * @depends testRankingCanBeFound
     */
    public function testMatchResultCanBeSubmitted(array $matchIds) : string
    {
        $matchId = array_shift($matchIds);
        $this->client->setBasicAuth('admin', '123456');
        $this->client->submitMatchResult($matchId, 3, 1);
        $this->client->clearAuth();
        $match = $this->client->getMatch($matchId);
        self::assertObjectHasAttribute('home_score', $match);
        self::assertObjectHasAttribute('guest_score', $match);
        self::assertObjectHasAttribute('home_team_id', $match);
        self::assertObjectHasAttribute('guest_team_id', $match);
        self::assertEquals(3, $match->home_score);
        self::assertEquals(1, $match->guest_score);

        $ranking = $this->client->getSeasonRanking($match->season_id);
        self::assertObjectHasAttribute('positions', $ranking);
        $positions = $ranking->positions;
        self::assertInternalType('array', $positions);

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
     * @return string
     */
    public function testTournamentCanBeCreated(): string
    {
        $this->client->setBasicAuth('admin', '123456');
        $response = $this->client->createTournament('Foo');
        self::assertObjectHasAttribute('id', $response);
        self::assertInternalType('string', $response->id);
        self::assertGreaterThan(0, strlen($response->id));

        return $response->id;
    }

    /**
     * @param string $tournamentId
     * @param array  $teamIds
     * @depends testTournamentCanBeCreated
     * @depends testTeamsCanBeCreated
     */
    public function testTournamentRoundsCanBeCreated(string $tournamentId, array $teamIds)
    {
        $this->client->setBasicAuth('admin', '123456');
        $firstRound = [
            ['home_team_id' => $teamIds[0], 'guest_team_id' => $teamIds[1]],
            ['home_team_id' => $teamIds[2], 'guest_team_id' => $teamIds[3]]
        ];
        $this->client->setTournamentRound($tournamentId, 1, $firstRound, '2018-03-01');
        $tournament = $this->client->getTournament($tournamentId);
        self::assertObjectHasAttribute('rounds', $tournament);
        self::assertEquals(1, $tournament->rounds);
        $matches = $this->client->getMatchesInTournament($tournamentId);
        self::assertEquals(2, count($matches));

        $secondRound = [
            ['home_team_id' => $teamIds[1], 'guest_team_id' => $teamIds[2]]
        ];
        $this->client->setTournamentRound($tournamentId, 2, $secondRound, '2018-03-08');
        $tournament = $this->client->getTournament($tournamentId);
        self::assertObjectHasAttribute('rounds', $tournament);
        self::assertEquals(2, $tournament->rounds);
        $matches = $this->client->getMatchesInTournament($tournamentId);
        self::assertEquals(3, count($matches));
    }

    public function testUserCanBeAuthenticated()
    {
        $this->client->setBasicAuth('admin', '123456');
        $user = $this->client->getAuthenticatedUser();
        self::assertObjectHasAttribute('email', $user);
        self::assertEquals('admin', $user->email);
    }

    public function testUserCanBeCreated()
    {
        $this->client->setBasicAuth('admin', '123456');
        $user = $this->client->createUser([
            'email' => 'nobody@example.com',
            'password' => 'secret',
            'first_name' => 'My Name Is',
            'last_name' => 'Nobody',
            'role' => 'team_manager',
            'teams' => []
        ]);
        self::assertObjectHasAttribute('id', $user);
    }
}