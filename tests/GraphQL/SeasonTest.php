<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Tests\Framework\Fixtures;

class SeasonTest extends TestCase
{
    private static $teamIds = [];

    public static function setUpBeforeClass(): void
    {
        $client = self::createClient();
        $client->useCredentials(Fixtures::ADMIN_USER_EMAIL, Fixtures::ADMIN_USER_PASSWORD);
        for ($i = 1; $i <= 8; $i++) {
            $teamId = 'Team' . $i;
            $client->createTeam($teamId, $teamId);
            self::$teamIds[] = $teamId;
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client->useCredentials(Fixtures::ADMIN_USER_EMAIL, Fixtures::ADMIN_USER_PASSWORD);
    }

    public function testSeasonCanBeCreated(): string
    {
        $sent = [
            'id' => 'SeasonA',
            'name' => 'Season 18/19'
        ];
        $this->client->createSeason($sent['id'], $sent['name']);

        $received = $this->client->getSeasonById($sent['id']);
        self::assertSame($sent['id'], $received->id);
        self::assertSame($sent['name'], $received->name);
        self::assertSame(Season::STATE_PREPARATION, $received->state);
        self::assertSame(0, $received->match_day_count);
        self::assertSame(0, $received->team_count);
        self::assertNull($received->ranking);

        $allSeasons = $this->client->getAllSeasons();
        self::assertArrayContainsObjectWithAttribute($allSeasons, 'id', $sent['id']);

        return $sent['id'];
    }

    /**
     * @depends testSeasonCanBeCreated
     * @param string $seasonId
     */
    public function testSeasonCanBeStarted(string $seasonId)
    {
        $teamIdSlice = array_slice(self::$teamIds, 0, 2);
        foreach ($teamIdSlice as $teamId) {
            $this->client->addTeamToSeason($seasonId, $teamId);
        }

        $season = $this->client->getSeasonById($seasonId);
        self::assertSame(2, $season->team_count);

        foreach ($teamIdSlice as $teamId) {
            $this->client->removeTeamFromSeason($seasonId, $teamId);
        }

        $season = $this->client->getSeasonById($seasonId);
        self::assertSame(0, $season->team_count);

        foreach (self::$teamIds as $teamId) {
            $this->client->addTeamToSeason($seasonId, $teamId);
        }

        $dates = self::createMatchDayDates(count(self::$teamIds) - 1);
        $this->client->createMatchesForSeason($seasonId, $dates);
        $this->client->startSeason($seasonId);

        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        self::assertSame($seasonId, $season->id);
        self::assertSame(count($dates), $season->match_day_count);
        self::assertSame(count(self::$teamIds), $season->team_count);
        self::assertSame(count($dates), count($season->match_days));

        $matchCount = 0;
        foreach ($season->match_days as $matchDay) {
            $matchCount += count($matchDay->matches);
        }
        self::assertSame(count($dates) * count(self::$teamIds) / 2, $matchCount);
    }

    private static function createMatchDayDates(int $count): array
    {
        $result = [];
        $start  = new \DateTime('2019-03-21');
        $end    = new \DateTime('2019-03-22');
        for ($i = 0; $i < $count; $i++) {
            $result[] = [
                'from' => $start->format('Y-m-d'),
                'to'   => $end->format('Y-m-d')
            ];
            $start->modify('+7 days');
            $end->modify('+7 days');
        }

        return $result;
    }
}