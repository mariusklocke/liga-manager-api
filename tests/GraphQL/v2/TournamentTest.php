<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\TournamentList;

class TournamentTest extends TestCase
{
    public function testTournamentsCanBeListed(): void
    {
        $response = self::$client->request(new TournamentList());

        self::assertObjectHasAttribute('data', $response);
        self::assertObjectHasAttribute('tournamentList', $response->data);
        self::assertIsArray($response->data->tournamentList);
        self::assertNotEmpty($response->data->tournamentList);

        foreach ($response->data->tournamentList as $tournament) {
            self::assertObjectHasAttribute('id', $tournament);
            self::assertObjectHasAttribute('name', $tournament);

            self::assertIsArray($tournament->matchDays);
            foreach ($tournament->matchDays as $matchDay) {
                self::assertObjectHasAttribute('id', $matchDay);
                self::assertObjectHasAttribute('number', $matchDay);
                self::assertIsArray($matchDay->matches);
                foreach ($matchDay->matches as $match) {
                    self::assertObjectHasAttribute('id', $match);
                }
            }
        }
    }
}
