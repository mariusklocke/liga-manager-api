<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateMatch;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateMatchDay;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateTournament;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\DeleteTournament;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\UpdateTournament;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\Tournament;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\TournamentList;
use HexagonalPlayground\Tests\Framework\IdGenerator;

class TournamentTest extends CompetitionTest
{
    public function testTournamentCanBeCreated(): string
    {
        $id = IdGenerator::generate();
        $name = __METHOD__;

        self::$client->request(new CreateTournament([
            'id' => $id,
            'name' => $name
        ]), $this->defaultAdminAuth);

        $tournament = $this->getTournament($id);
        self::assertIsObject($tournament);
        self::assertEquals($id, $tournament->id);
        self::assertEquals($name, $tournament->name);

        return $id;
    }

    /**
     * @depends testTournamentCanBeCreated
     * @param string $id
     * @return string
     */
    public function testTournamentCanBeUpdated(string $id): string
    {
        $name = __METHOD__;

        self::$client->request(new UpdateTournament([
            'id' => $id,
            'name' => $name
        ]), $this->defaultAdminAuth);

        $tournament = $this->getTournament($id);
        self::assertIsObject($tournament);
        self::assertEquals($id, $tournament->id);
        self::assertEquals($name, $tournament->name);

        return $id;
    }

    /**
     * @depends testTournamentCanBeUpdated
     * @param string $tournamentId
     * @return string
     */
    public function testMatchDaysCanBeCreated(string $tournamentId): string
    {
        $matchDayId = IdGenerator::generate();
        $startDate = new \DateTime('next saturday');
        $endDate = (clone $startDate)->modify('+1 day');
        $datePeriod = ['from' => $this->formatDate($startDate), 'to' => $this->formatDate($endDate)];
        $number = 1;

        self::$client->request(new CreateMatchDay([
            'id' => $matchDayId,
            'tournamentId' => $tournamentId,
            'number' => $number,
            'datePeriod' => $datePeriod
        ]), $this->defaultAdminAuth);

        $tournament = $this->getTournament($tournamentId);
        self::assertIsObject($tournament);
        self::assertCount(1, $tournament->matchDays);

        return $tournamentId;
    }

    /**
     * @depends testMatchDaysCanBeCreated
     * @param string $tournamentId
     * @return string
     */
    public function testMatchesCanBeCreated(string $tournamentId): string
    {
        $tournament = $this->getTournament($tournamentId);
        self::assertIsObject($tournament);
        self::assertCount(1, $tournament->matchDays);
        $matchDay = current($tournament->matchDays);

        foreach (array_chunk(self::$teamIds, 2) as $chunk) {
            list($homeTeamId, $guestTeamId) = $chunk;
            $matchId = IdGenerator::generate();

            self::$client->request(new CreateMatch([
                'id' => $matchId,
                'matchDayId' => $matchDay->id,
                'homeTeamId' => $homeTeamId,
                'guestTeamId' => $guestTeamId
            ]), $this->defaultAdminAuth);
        }

        $tournament = $this->getTournament($tournamentId);
        self::assertIsObject($tournament);
        self::assertCount(1, $tournament->matchDays);
        $matchDay = current($tournament->matchDays);
        self::assertIsArray($matchDay->matches);
        self::assertCount(count(self::$teamIds) / 2, $matchDay->matches);

        return $tournamentId;
    }

    /**
     * @depends testMatchesCanBeCreated
     * @param string $id
     */
    public function testTournamentCanBeDeleted(string $id): void
    {
        self::assertNotNull($this->getTournament($id));
        self::$client->request(new DeleteTournament([
            'id' => $id
        ]), $this->defaultAdminAuth);
        self::assertNull($this->getTournament($id));
    }

    public function testTournamentsCanBeListed(): void
    {
        $tournamentList = self::$client->request(new TournamentList());

        self::assertIsArray($tournamentList);
        self::assertNotEmpty($tournamentList);
        foreach ($tournamentList as $tournament) {
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

    private function getTournament(string $id): ?object
    {
        return self::$client->request(new Tournament(['id' => $id]));
    }
}