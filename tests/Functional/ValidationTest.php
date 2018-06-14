<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

use HexagonalPlayground\Tests\Functional\Framework\ApiException;

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

    public function testCreatingMatchesWithInvalidStartDateFails()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $response = $this->client->createSeason('Foo');
        $seasonId = $response->id;
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $this->client->createMatches($seasonId, 'foobar');
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
        $this->client->setTournamentRound($tournamentId, 1, $teamPairs, 'no real date');
    }

    public function testSettingTournamentRoundWithEmptyTeamPairsFails()
    {
        $this->client->setBasicAuth('admin@example.com', '123456');
        $response = $this->client->createTournament('Bar');
        $tournamentId = $response->id;
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $this->client->setTournamentRound($tournamentId, 1, [], '2018-04-01');
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
        $this->client->setTournamentRound($tournamentId, 1, $teamPairs, '2018-04-01');
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