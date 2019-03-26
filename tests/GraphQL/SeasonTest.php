<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Tests\Framework\GraphQL\Exception;

class SeasonTest extends CompetitionTestCase
{
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
     * @return string
     */
    public function testSeasonCanBeStarted(string $seasonId): string
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

        return $season->id;
    }

    /**
     * @depends testSeasonCanBeStarted
     * @param string $seasonId
     */
    public function testSubmittingMatchResultByNonParticipatingTeamFails(string $seasonId): void
    {
        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        $matchId = $season->match_days[0]->matches[0]->id;
        $match = $this->client->getMatchById($matchId);
        $nonParticipatingTeamIds = $this->getNonParticipatingTeamIds($match);

        $this->useTeamManagerAuth(array_shift($nonParticipatingTeamIds));
        $this->expectException(Exception::class);
        $this->client->submitMatchResult($matchId, 4, 3);
    }

    /**
     * @depends testSeasonCanBeStarted
     * @param string $seasonId
     * @return string
     */
    public function testSubmittingMatchResultAffectsRanking(string $seasonId): string
    {
        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        $matchId = $season->match_days[0]->matches[0]->id;
        $match = $this->client->getMatchById($matchId);
        self::assertNotNull($match);
        $this->useTeamManagerAuth($match->home_team->id);
        $this->client->submitMatchResult($matchId, 1, 1);

        $season = $this->client->getSeasonById($seasonId);
        self::assertNotNull($season->ranking);

        foreach ($season->ranking->positions as $position) {
            if ($position->team->id === $match->home_team->id || $position->team->id === $match->guest_team->id) {
                self::assertSame(1, $position->number);
                self::assertSame(0, $position->losses);
                self::assertSame(1, $position->draws);
                self::assertSame(0, $position->wins);
                self::assertSame(1, $position->scored_goals);
                self::assertSame(1, $position->conceded_goals);
                self::assertSame(1, $position->points);
            } else {
                self::assertGreaterThan(2, $position->number);
                self::assertSame(0, $position->losses);
                self::assertSame(0, $position->draws);
                self::assertSame(0, $position->wins);
                self::assertSame(0, $position->scored_goals);
                self::assertSame(0, $position->conceded_goals);
                self::assertSame(0, $position->points);
            }
        }

        $now = time();
        $updatedAt = strtotime($season->ranking->updated_at);
        self::assertLessThan(5, $now - $updatedAt);

        return $seasonId;
    }

    /**
     * @depends testSubmittingMatchResultAffectsRanking
     * @param string $seasonId
     */
    public function testCancellingMatchByNonParticipatingTeamFails(string $seasonId): void
    {
        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        $matchId = $season->match_days[0]->matches[0]->id;
        $match = $this->client->getMatchById($matchId);
        $nonParticipatingTeamIds = $this->getNonParticipatingTeamIds($match);

        $this->useTeamManagerAuth(array_shift($nonParticipatingTeamIds));
        $this->expectException(Exception::class);
        $this->client->cancelMatch($matchId, 'Just cause');
    }

    /**
     * @depends testSubmittingMatchResultAffectsRanking
     * @param string $seasonId
     * @return string
     */
    public function testCancellingMatchAffectsRanking(string $seasonId): string
    {
        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        $matchId = $season->match_days[0]->matches[0]->id;
        $match = $this->client->getMatchById($matchId);
        self::assertNotNull($match);
        $this->useTeamManagerAuth($match->home_team->id);
        $this->client->cancelMatch($matchId, 'Team did not show up');

        $season = $this->client->getSeasonById($seasonId);
        self::assertNotNull($season->ranking);

        foreach ($season->ranking->positions as $position) {
            self::assertSame(0, $position->losses);
            self::assertSame(0, $position->draws);
            self::assertSame(0, $position->wins);
            self::assertSame(0, $position->scored_goals);
            self::assertSame(0, $position->conceded_goals);
            self::assertSame(0, $position->points);
        }

        $now = time();
        $updatedAt = strtotime($season->ranking->updated_at);
        self::assertLessThan(5, $now - $updatedAt);

        return $seasonId;
    }

    private function getNonParticipatingTeamIds(\stdClass $match): array
    {
        return array_diff(self::$teamIds, [
            $match->home_team->id,
            $match->guest_team->id
        ]);
    }
}