<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

use HexagonalPlayground\Domain\MatchResultSubmitted;

class BasicUseCaseTest extends TestCase
{
    public function testSeasonCanBeCreated(): string
    {
        $response = $this->client->createSeason('bar');
        self::assertObjectHasAttribute('id', $response);
        self::assertGreaterThan(0, strlen($response->id));
        return $response->id;
    }

    public function testTeamsCanBeCreated(): array
    {
        $teamIds = [];
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
     * @return string
     * @depends testMatchesCanBeFound
     * @depends testRankingCanBeFound
     */
    public function testMatchResultCanBeSubmitted(array $matchIds) : string
    {
        $matchId = array_shift($matchIds);
        $this->client->setBasicAuth();
        $this->client->submitMatchResult($matchId, 3, 1);
        $this->client->clearAuth();
        $events = $this->getEventStore()->findMany();
        self::assertCount(1, $events);
        /** @var MatchResultSubmitted $event */
        $event = $events[0];
        self::assertInstanceOf(MatchResultSubmitted::class, $event);
        self::assertEquals(3, $event->getHomeScore());
        self::assertEquals(1, $event->getGuestScore());

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
        $firstRound = [
            ['home_team_id' => $teamIds[0], 'guest_team_id' => $teamIds[1]],
            ['home_team_id' => $teamIds[2], 'guest_team_id' => $teamIds[3]]
        ];
        $this->client->setTournamentRound($tournamentId, 1, $firstRound, new \DateTimeImmutable('2018-03-01'));
        $tournament = $this->client->getTournament($tournamentId);
        self::assertObjectHasAttribute('rounds', $tournament);
        self::assertEquals(1, $tournament->rounds);
        $matches = $this->client->getMatchesInTournament($tournamentId);
        self::assertEquals(2, count($matches));

        $secondRound = [
            ['home_team_id' => $teamIds[1], 'guest_team_id' => $teamIds[2]]
        ];
        $this->client->setTournamentRound($tournamentId, 2, $secondRound, new \DateTimeImmutable('2018-03-08'));
        $tournament = $this->client->getTournament($tournamentId);
        self::assertObjectHasAttribute('rounds', $tournament);
        self::assertEquals(2, $tournament->rounds);
        $matches = $this->client->getMatchesInTournament($tournamentId);
        self::assertEquals(3, count($matches));
    }

    public function testUserCanBeAuthenticated()
    {
        $this->client->setBasicAuth();
        $user = $this->client->getAuthenticatedUser();
        self::assertObjectHasAttribute('email', $user);
        self::assertEquals('admin', $user->email);
    }
}