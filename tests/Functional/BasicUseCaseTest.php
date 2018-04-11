<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

class BasicUseCaseTest extends TestCase
{
    public function testSeasonCanBeCreated() : string
    {
        $client = static::getClient();
        $response = $client->post('/api/season', ['name' => 'bar']);
        self::assertEquals(200, $response->getStatusCode());
        $data = $client->parseBody($response->getBody());
        self::assertArrayHasKey('id', $data);
        self::assertGreaterThan(0, strlen($data['id']));

        return $data['id'];
    }

    public function testTeamsCanBeCreated() : array
    {
        $client = static::getClient();
        $response = $client->post('/api/team', []);
        self::assertEquals(400, $response->getStatusCode());
        $teamIds = [];
        for ($i = 1; $i <= 4; $i++) {
            $response = $client->post('/api/team', ['name' => 'Team No. ' . $i]);
            self::assertEquals(200, $response->getStatusCode());
            $data = $client->parseBody($response->getBody());
            self::assertArrayHasKey('id', $data);
            $teamIds[] = $data['id'];
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
        $client = static::getClient();
        $response = $client->get('/api/season');
        self::assertEquals(200, $response->getStatusCode());
        $seasonList = $client->parseBody($response->getBody());
        self::assertTrue(is_array($seasonList));

        $found = false;
        foreach ($seasonList as $season) {
            self::assertArrayHasKey('id', $season);
            self::assertArrayHasKey('name', $season);
            self::assertArrayHasKey('state', $season);
            if (!$found && $season['id'] === $seasonId) {
                $found = true;
            }
        }
        self::assertTrue($found);

        $response = $client->get('/api/season/' . $seasonId);
        $season = $client->parseBody($response->getBody());
        self::assertEquals(200, $response->getStatusCode());
        self::assertArrayHasKey('id', $season);
        self::assertArrayHasKey('name', $season);
        self::assertArrayHasKey('state', $season);
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
        $client = static::getClient();
        foreach ($teamIds as $teamId) {
            $uri = sprintf('/api/season/%s/team/%s', $seasonId, $teamId);
            $response = $client->put($uri);
            self::assertEquals(204, $response->getStatusCode());
        }

        return $seasonId;
    }

    /**
     * @param string $seasonId
     * @return string
     * @depends testTeamsCanBeAddedToSeason
     */
    public function testMatchesCanBeCreated(string $seasonId) : string
    {
        $client = static::getClient();
        $response = $client->post('/api/season/' . $seasonId . '/matches', [
            'start_at' => '2018-03-02'
        ]);
        self::assertEquals(204, $response->getStatusCode());
        return $seasonId;
    }

    /**
     * @param string $seasonId
     * @return array
     * @depends testMatchesCanBeCreated
     */
    public function testMatchesCanBeFound(string $seasonId) : array
    {
        $client = static::getClient();
        $queryParams = [
            'match_day' => 1
        ];
        $response = $client->get('/api/season/' . $seasonId . '/matches?' . http_build_query($queryParams));
        self::assertEquals(200, $response->getStatusCode());
        $matches = $client->parseBody($response->getBody());
        self::assertEquals(2, count($matches));
        $matchIds = [];
        foreach ($matches as $match) {
            self::assertArrayHasKey('id', $match);
            $matchIds[] = $match['id'];
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
        $client = static::getClient();
        $response = $client->post('/api/season/' . $seasonId . '/start');
        self::assertEquals(204, $response->getStatusCode());
        return $seasonId;
    }

    /**
     * @param string $seasonId
     *
     * @depends testSeasonCanBeStarted
     */
    public function testRankingCanBeFound(string $seasonId)
    {
        $client = static::getClient();
        $response = $client->get('/api/season/' . $seasonId . '/ranking');
        self::assertEquals(200, $response->getStatusCode());
        $ranking = $client->parseBody($response->getBody());
        self::assertArrayHasKey('positions', $ranking);
        self::assertArrayHasKey('updated_at', $ranking);
        $positions = $ranking['positions'];
        self::assertTrue(is_array($positions));
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
                self::assertArrayHasKey($expectedProperty, $position);
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
        $client = static::getClient();
        $matchResult = [
            'home_score' => 3,
            'guest_score' => 1
        ];
        $authHelper = new AuthHelper();
        $headers = $authHelper->getBasicAuthHeaders();
        $response = $client->post('/api/match/' . $matchId . '/result', $matchResult, $headers);
        self::assertEquals(204, $response->getStatusCode());
        return $matchId;
    }

    /**
     * @param string $matchId
     * @return array
     * @depends testMatchResultCanBeSubmitted
     */
    public function testMatchResultCanBeFound(string $matchId) : array
    {
        $client = static::getClient();
        $response = $client->get('/api/match/' . $matchId);
        $match = $client->parseBody($response->getBody());
        self::assertTrue(is_array($match));
        self::assertArrayHasKey('home_score', $match);
        self::assertArrayHasKey('guest_score', $match);
        self::assertArrayHasKey('home_team_id', $match);
        self::assertArrayHasKey('guest_team_id', $match);
        self::assertEquals(3, $match['home_score']);
        self::assertEquals(1, $match['guest_score']);

        return $match;
    }

    /**
     * @param array $match
     * @depends testMatchResultCanBeFound
     */
    public function testRankingReflectsLatestMatchResults(array $match)
    {
        $client  = static::getClient();
        $response = $client->get('/api/season/' . $match['season_id'] . '/ranking');
        $ranking  = $client->parseBody($response->getBody());
        self::assertArrayHasKey('positions', $ranking);
        self::assertArrayHasKey('updated_at', $ranking);

        $positions = $ranking['positions'];
        self::assertTrue(is_array($positions));
        $count = 0;
        $found = false;
        foreach ($positions as $position) {
            if (!$found && $position['wins'] === 1) {
                $found = $position['scored_goals'] === 3 && $position['conceded_goals'] === 1;
            }
            $count++;
        }
        self::assertTrue($found);
        self::assertEquals(4, $count);
    }
}