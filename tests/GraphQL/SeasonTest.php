<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use DateTimeImmutable;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Tests\Framework\EventCapturer;
use stdClass;

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

    public function testSeasonCanBeDeleted(): void
    {
        $seasonId = 'toDelete';
        $this->client->createSeason($seasonId, $seasonId);
        $season = $this->client->getSeasonById($seasonId);
        self::assertNotNull($season);

        $this->client->deleteSeason($seasonId);
        $season = $this->client->getSeasonById($seasonId);
        self::assertNull($season);
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

        $dates = self::createMatchDayDates(2 * (count(self::$teamIds) - 1));
        $this->client->createMatchesForSeason($seasonId, $dates);

        $events = EventCapturer::getInstance()->capture(function() use ($seasonId) {
            $this->client->startSeason($seasonId);
        });

        self::assertCount(1, $events);
        self::assertSame('season:started', $events[0]->getType());

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
     * @return string
     */
    public function testAllMatchesCanBeScheduledAtOnce(string $seasonId): string
    {
        $appointments = $this->createMatchAppointments();
        $this->client->scheduleAllMatchesForSeason($seasonId, $appointments);

        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        foreach ($season->match_days as $matchDay) {
            foreach ($matchDay->matches as $match) {
                $match = $this->client->getMatchById($match->id);

                self::assertNotNull($match);
                self::assertNotNull($match->pitch);
                self::assertNotNull($match->kickoff);
            }
        }

        return $seasonId;
    }

    /**
     * @depends testAllMatchesCanBeScheduledAtOnce
     * @param string $seasonId
     * @return string
     */
    public function testMatchCanBeLocated(string $seasonId): string
    {
        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        $matchId = $season->match_days[0]->matches[0]->id;
        $match = $this->client->getMatchById($matchId);

        self::assertNotNull($match);

        $pitchId = 'PitchABC';
        $this->client->createPitch($pitchId, 'Pitch ABC', 12.34, 23.45);
        $this->client->locateMatch($matchId, $pitchId);

        $match = $this->client->getMatchById($matchId);
        self::assertNotNull($match);
        self::assertNotNull($match->pitch);
        self::assertSame($pitchId, $match->pitch->id);

        return $matchId;
    }

    /**
     * @depends testMatchCanBeLocated
     * @param string $matchId
     * @return string
     */
    public function testMatchCanBeScheduled(string $matchId): string
    {
        $match = $this->client->getMatchById($matchId);

        self::assertNotNull($match);

        $newKickoff = '2019-04-05T11:23:44+02:00';
        self::assertNotEquals($newKickoff, $match->kickoff);

        $this->client->scheduleMatch($matchId, $newKickoff);

        $match = $this->client->getMatchById($matchId);
        self::assertNotNull($match);
        self::assertNotNull($match->kickoff);
        self::assertSame('2019-04-05T09:23:44Z', $match->kickoff);

        return $matchId;
    }

    /**
     * @depends testMatchCanBeScheduled
     * @param string $matchId
     */
    public function testDeletingUsedPitchFails(string $matchId): void
    {
        $match = $this->client->getMatchById($matchId);
        self::assertNotNull($match);
        self::assertNotNull($match->pitch);

        $this->expectClientException();
        $this->client->deletePitch($match->pitch->id);
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
        $this->expectClientException();
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
        $this->expectClientException();
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

    /**
     * @depends testCancellingMatchAffectsRanking
     * @depends testCancellingMatchByNonParticipatingTeamFails
     * @param string $seasonId
     * @return string
     */
    public function testPenaltiesAffectRanking(string $seasonId): string
    {
        $penaltyId = 'foobar';
        $teamId = self::$teamIds[0];

        $this->client->addRankingPenalty($penaltyId, $seasonId, $teamId, 'for not partying hard', 5);
        $season = $this->client->getSeasonById($seasonId);
        $positions = array_filter($season->ranking->positions, function($position) use ($teamId) {
            return $position->team->id === $teamId;
        });
        self::assertSame(1, count($positions));

        $position = array_shift($positions);
        self::assertSame(-5, $position->points);

        $this->client->removeRankingPenalty($penaltyId, $seasonId);
        $season = $this->client->getSeasonById($seasonId);
        $positions = array_filter($season->ranking->positions, function($position) use ($teamId) {
            return $position->team->id === $teamId;
        });
        self::assertSame(1, count($positions));

        $position = array_shift($positions);
        self::assertSame(0, $position->points);

        return $seasonId;
    }

    /**
     * @depends testPenaltiesAffectRanking
     * @param string $seasonId
     * @return string
     */
    public function testMatchDayCanBeRescheduled(string $seasonId): string
    {
        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        self::assertGreaterThan(0, count($season->match_days));
        $matchDay = $season->match_days[0];
        $matchDayId = $matchDay->id;

        $newStart = (new DateTimeImmutable($matchDay->start_date))->modify('+7 days');
        $newEnd   = $newStart->modify('+1 day');

        $this->client->rescheduleMatchDay($matchDayId, [
            'from' => $newStart->format(DATE_ATOM),
            'to'   => $newEnd->format(DATE_ATOM)
        ]);

        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        self::assertGreaterThan(0, count($season->match_days));
        $matchDay = $season->match_days[0];

        self::assertSame($matchDayId, $matchDay->id);
        self::assertEquals($newStart->format('U'), strtotime($matchDay->start_date));
        self::assertEquals($newEnd->format('U'), strtotime($matchDay->end_date));

        return $seasonId;
    }

    /**
     * @depends testMatchDayCanBeRescheduled
     * @param string $seasonId
     * @return string
     */
    public function testTeamCanBeReplacedWhileSeasonInProgress(string $seasonId): string
    {
        $seasonBefore = $this->client->getSeasonByIdWithMatchDays($seasonId);
        self::assertSame(Season::STATE_PROGRESS, $seasonBefore->state);
        $retiringTeamId = $seasonBefore->ranking->positions[0]->team->id;
        $spareTeamId = self::$spareTeamIds[0];
        self::assertNotEquals($spareTeamId, $retiringTeamId);

        $this->client->replaceTeamInSeason($seasonId, $retiringTeamId, $spareTeamId);

        $seasonAfter = $this->client->getSeasonByIdWithMatchDays($seasonId);
        self::assertSame($spareTeamId, $seasonAfter->ranking->positions[0]->team->id);

        return $seasonId;
    }

    /**
     * @depends testTeamCanBeReplacedWhileSeasonInProgress
     * @param string $seasonId
     */
    public function testEndedSeasonsRankingIsFinal(string $seasonId): void
    {
        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        self::assertSame(Season::STATE_PROGRESS, $season->state);
        $match = $season->match_days[0]->matches[1];

        $this->client->endSeason($seasonId);
        $season = $this->client->getSeasonById($seasonId);
        self::assertSame(Season::STATE_ENDED, $season->state);

        $this->expectClientException();
        $this->client->submitMatchResult($match->id, 2, 3);
    }

    private function getNonParticipatingTeamIds(stdClass $match): array
    {
        return array_diff(self::$teamIds, [
            $match->home_team->id,
            $match->guest_team->id
        ]);
    }
}
