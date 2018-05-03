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
}