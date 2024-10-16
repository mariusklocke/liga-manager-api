<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Tests\Framework\DataGenerator;
use PHPUnit\Framework\Attributes\Depends;

class TournamentTest extends CompetitionTestCase
{
    public function testTournamentCanBeCreated(): string
    {
        $tournamentId = DataGenerator::generateId();
        $this->client->createTournament($tournamentId, $tournamentId);
        $events = $this->client->getLatestEvents();
        $events = array_filter($events, function ($event) {
            return $event->type === 'tournament:created';
        });
        self::assertGreaterThanOrEqual(1, count($events));

        $tournament = $this->client->getTournamentById($tournamentId);
        self::assertNotNull($tournament);
        self::assertSame($tournamentId, $tournament->id);
        self::assertSame($tournamentId, $tournament->name);

        $allTournaments = $this->client->getAllTournaments();
        self::assertArrayContainsObjectWithAttribute($allTournaments, 'id', $tournamentId);

        return $tournamentId;
    }

    /**
     * @param string $tournamentId
     * @return string
     */
    #[Depends("testTournamentCanBeCreated")]
    public function testTournamentRoundsCanBeCreated(string $tournamentId): string
    {
        $datePeriod = [
            'from' => '2019-03-31',
            'to'   => '2019-04-01'
        ];
        $teamPairs = self::getTeamPairs();
        $this->client->setTournamentRound($tournamentId, 1, $teamPairs, $datePeriod);

        $tournament = $this->client->getTournamentByIdWithRounds($tournamentId);
        self::assertNotNull($tournament);
        self::assertSame(1, count($tournament->rounds));

        return $tournamentId;
    }

    /**
     * @param string $tournamentId
     */
    #[Depends("testTournamentRoundsCanBeCreated")]
    public function testTournamentCanBeDeleted(string $tournamentId): void
    {
        $countBefore = count($this->client->getAllTournaments());

        $this->client->deleteTournament($tournamentId);
        $tournament = $this->client->getTournamentById($tournamentId);
        self::assertNull($tournament);

        self::assertSame($countBefore - 1, count($this->client->getAllTournaments()));
    }

    private static function getTeamPairs(): array
    {
        $pairs = [];
        foreach (array_chunk(self::$teamIds, 2) as $chunk) {
            $pairs[] = [
                'home_team_id' => $chunk[0],
                'guest_team_id' => $chunk[1]
            ];
        }
        return $pairs;
    }
}
