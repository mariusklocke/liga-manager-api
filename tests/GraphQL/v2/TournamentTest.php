<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use DateTimeImmutable;
use HexagonalPlayground\Tests\Framework\DataGenerator;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateMatch;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateMatchDay;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\CreateTournament;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\DeleteMatch;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\DeleteMatchDay;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\DeleteTournament;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\UpdateMatchDay;
use HexagonalPlayground\Tests\Framework\GraphQL\Mutation\v2\UpdateTournament;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\Tournament;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\TournamentList;

class TournamentTest extends CompetitionTest
{
    public function testTournamentCanBeCreated(): string
    {
        $id = DataGenerator::generateId();
        $name = DataGenerator::generateString(8);

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
        $name = DataGenerator::generateString(8);

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
        $matchDayId = DataGenerator::generateId();
        $startDate = new DateTimeImmutable('next saturday');
        $endDate = $startDate->modify('+1 day');
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
    public function testMatchDaysCanBeUpdated(string $tournamentId): string
    {
        $tournament = $this->getTournament($tournamentId);
        self::assertIsObject($tournament);
        $matchDay = $this->getMatchDayByNumber($tournament, 1);
        self::assertIsObject($matchDay);

        $updatedDatePeriod = [
            'from' => (new DateTimeImmutable($matchDay->startDate))->modify('+1 week'),
            'to' => (new DateTimeImmutable($matchDay->endDate))->modify('+1 week')
        ];

        self::$client->request(new UpdateMatchDay([
            'id' => $matchDay->id,
            'datePeriod' => [
                'from' => self::formatDate($updatedDatePeriod['from']),
                'to' => self::formatDate($updatedDatePeriod['to'])
            ]
        ]), $this->defaultAdminAuth);

        $tournament = $this->getTournament($tournamentId);
        self::assertIsObject($tournament);
        $matchDay = $this->getMatchDayByNumber($tournament, 1);
        self::assertIsObject($matchDay);

        self::assertEquals(self::formatDate($updatedDatePeriod['from']), $matchDay->startDate);
        self::assertEquals(self::formatDate($updatedDatePeriod['to']), $matchDay->endDate);

        return $tournamentId;
    }

    /**
     * @depends testMatchDaysCanBeUpdated
     * @param string $tournamentId
     * @return string
     */
    public function testMatchDaysCanDeDeleted(string $tournamentId): string
    {
        $matchDayId = DataGenerator::generateId();
        $startDate = (new DateTimeImmutable('next saturday'))->modify('+1 week');
        $endDate = $startDate->modify('+1 day');
        $datePeriod = ['from' => $this->formatDate($startDate), 'to' => $this->formatDate($endDate)];
        $number = 2;

        self::$client->request(new CreateMatchDay([
            'id' => $matchDayId,
            'tournamentId' => $tournamentId,
            'number' => $number,
            'datePeriod' => $datePeriod
        ]), $this->defaultAdminAuth);

        $tournament = $this->getTournament($tournamentId);
        self::assertIsObject($tournament);
        $matchDay = $this->getMatchDayByNumber($tournament, 2);
        self::assertIsObject($matchDay);

        self::$client->request(new DeleteMatchDay([
            'id' => $matchDayId
        ]), $this->defaultAdminAuth);

        $tournament = $this->getTournament($tournamentId);
        self::assertIsObject($tournament);
        $matchDay = $this->getMatchDayByNumber($tournament, 2);
        self::assertNull($matchDay);

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
            $matchId = DataGenerator::generateId();

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
     * @param string $tournamentId
     * @return string
     */
    public function testMatchesCanBeDeleted(string $tournamentId): string
    {
        $tournament = $this->getTournament($tournamentId);
        self::assertIsObject($tournament);

        self::assertNotEmpty($tournament->matchDays);
        foreach ($tournament->matchDays as $matchDay) {
            self::assertNotEmpty($matchDay->matches);

            foreach ($matchDay->matches as $match) {
                self::$client->request(new DeleteMatch([
                    'id' => $match->id
                ]), $this->defaultAdminAuth);
            }
        }

        $tournament = $this->getTournament($tournamentId);
        self::assertIsObject($tournament);

        self::assertNotEmpty($tournament->matchDays);
        foreach ($tournament->matchDays as $matchDay) {
            self::assertEmpty($matchDay->matches);
        }

        return $tournamentId;
    }

    /**
     * @depends testMatchesCanBeDeleted
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
            self::assertObjectHasProperty('id', $tournament);
            self::assertObjectHasProperty('name', $tournament);

            self::assertIsArray($tournament->matchDays);
            foreach ($tournament->matchDays as $matchDay) {
                self::assertObjectHasProperty('id', $matchDay);
                self::assertObjectHasProperty('number', $matchDay);
                self::assertIsArray($matchDay->matches);
                foreach ($matchDay->matches as $match) {
                    self::assertObjectHasProperty('id', $match);
                }
            }
        }
    }

    private function getTournament(string $id): ?object
    {
        return self::$client->request(new Tournament(['id' => $id]));
    }

    private function getMatchDayByNumber(object $tournament, int $number): ?object
    {
        foreach ($tournament->matchDays as $matchDay) {
            if ($matchDay->number === $number) {
                return $matchDay;
            }
        }

        return null;
    }
}
