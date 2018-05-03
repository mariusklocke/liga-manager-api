<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

use HexagonalPlayground\Tests\Functional\Framework\ApiException;

class ValidationTest extends TestCase
{
    public function testCreatingSeasonWithEmptyNameFails()
    {
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $this->client->createSeason('');
    }

    public function testCreatingTeamWithEmptyNameFails()
    {
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $this->client->createTeam('');
    }

    public function testCreatingTournamentWithTooLongNameFails()
    {
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $this->client->createTournament(str_repeat('A', 256));
    }

    public function testCreatingMatchesWithInvalidStartDateFails()
    {
        $response = $this->client->createSeason('Foo');
        $seasonId = $response->id;
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $this->client->createMatches($seasonId, 'foobar');
    }

    public function testSettingTournamentRoundWithInvalidPlanningDateFails()
    {
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
        $response = $this->client->createTournament('Bar');
        $tournamentId = $response->id;
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $this->client->setTournamentRound($tournamentId, 1, [], '2018-04-01');
    }

    public function testSettingTournamentRoundWithTooManyTeamPairsFails()
    {
        $response = $this->client->createTournament('Bar');
        $tournamentId = $response->id;
        self::expectException(ApiException::class);
        self::expectExceptionCode(400);
        $teamPairs = array_fill(0, 100, [
            'home_team_id' => '', 'guest_team_id' => ''
        ]);
        $this->client->setTournamentRound($tournamentId, 1, $teamPairs, '2018-04-01');
    }
}