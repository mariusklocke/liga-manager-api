<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\REST;

use HexagonalPlayground\Tests\Framework\ApiException;

class ValidationTest extends TestCase
{
    public function testCreatingSeasonWithEmptyNameFails()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $this->client->createSeason('');
    }

    public function testCreatingTeamWithEmptyNameFails()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $this->client->createTeam('');
    }

    public function testCreatingTournamentWithTooLongNameFails()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $this->client->createTournament(str_repeat('A', 256));
    }

    public function testCreatingMatchesWithInvalidDateFails()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $response = $this->client->createSeason('Foo');
        $seasonId = $response->id;
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $matchDays = [['from' => 'foo', 'to' => 'bar']];
        $this->client->createMatches($seasonId, $matchDays);
    }

    public function testSettingTournamentRoundWithInvalidPlanningDateFails()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $response = $this->client->createTournament('Bar');
        $tournamentId = $response->id;
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $teamPairs = [
            'home_team_id' => '', 'guest_team_id' => ''
        ];
        $this->client->setTournamentRound($tournamentId, 1, $teamPairs, ['from' => 'foo', 'to' => 'bar']);
    }

    public function testSettingTournamentRoundWithEmptyTeamPairsFails()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $response = $this->client->createTournament('Bar');
        $tournamentId = $response->id;
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $this->client->setTournamentRound($tournamentId, 1, [], ['from' => '2018-04-01', 'to' => '2018-04-01']);
    }

    public function testSettingTournamentRoundWithTooManyTeamPairsFails()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $response = $this->client->createTournament('Bar');
        $tournamentId = $response->id;
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $teamPairs = array_fill(0, 100, [
            'home_team_id' => '', 'guest_team_id' => ''
        ]);
        $this->client->setTournamentRound($tournamentId, 1, $teamPairs, ['from' => '2018-04-01', 'to' => '2018-04-01']);
    }

    public function testCreatingUserWithInvalidEmailFails()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $this->client->createUser([
            'email' => 'foo123bar',
            'password' => 'secret',
            'first_name' => 'My Name Is',
            'last_name' => 'Nobody',
            'role' => 'team_manager',
            'teams' => []
        ]);
    }
}