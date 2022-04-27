<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

class TournamentTest extends TestCase
{
    public function testTournamentsCanBeListed(): void
    {
        $query = self::$client->createQuery('tournamentList')
            ->fields([
                'id',
                'name',
                'matchDays' => [
                    'id',
                    'number',
                    'matches' => [
                        'id'
                    ]
                ]
            ]);

        $response = self::$client->request($query);

        self::assertResponseNotHasError($response);
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
