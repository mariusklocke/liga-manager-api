<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use DateTimeImmutable;
use DateTimeZone;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Tests\Framework\IdGenerator;
use PHPUnit\Framework\Attributes\Depends;
use stdClass;

class SeasonTest extends CompetitionTestCase
{
    public function testSeasonCanBeCreated(): string
    {
        $sent = [
            'id' => IdGenerator::generate(),
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

        $events = $this->client->getLatestEvents();
        self::assertArrayContainsObjectWithAttribute($events, 'type', 'season:created');

        return $sent['id'];
    }

    public function testSeasonCanBeDeleted(): void
    {
        $seasonId = IdGenerator::generate();
        $this->client->createSeason($seasonId, $seasonId);
        $season = $this->client->getSeasonById($seasonId);
        self::assertNotNull($season);

        $this->client->deleteSeason($seasonId);
        $season = $this->client->getSeasonById($seasonId);
        self::assertNull($season);
    }

    /**
     * @param string $seasonId
     * @return string
     */
    #[Depends("testSeasonCanBeCreated")]
    public function testSeasonCanBeStarted(string $seasonId): string
    {
        $teamIdSlice = array_slice(self::$teamIds, 0, 2);
        foreach ($teamIdSlice as $teamId) {
            $this->client->addTeamToSeason($seasonId, $teamId);
        }

        $season = $this->client->getSeasonById($seasonId);
        self::assertSame(2, $season->team_count);
        self::assertSame(2, count($season->teams));

        foreach ($teamIdSlice as $teamId) {
            $this->client->removeTeamFromSeason($seasonId, $teamId);
        }

        $season = $this->client->getSeasonById($seasonId);
        self::assertSame(0, $season->team_count);
        self::assertSame(0, count($season->teams));

        foreach (self::$teamIds as $teamId) {
            $this->client->addTeamToSeason($seasonId, $teamId);
        }

        $dates = self::createMatchDayDates(2 * (count(self::$teamIds) - 1));
        $this->client->createMatchesForSeason($seasonId, $dates);

        $events = self::catchEvents(Event::class, function () use ($seasonId) {
            $this->client->startSeason($seasonId);
        });

        self::assertCount(1, $events);
        self::assertSame('season:started', $events[0]->getType());

        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        self::assertSame($seasonId, $season->id);
        self::assertSame(count($dates), $season->match_day_count);
        self::assertSame(count(self::$teamIds), $season->team_count);
        self::assertSame(count(self::$teamIds), count($season->teams));
        self::assertSame(count($dates), count($season->match_days));

        $matchCount = 0;
        $previousMatchDay = null;
        foreach ($season->match_days as $matchDay) {
            $matchCount += count($matchDay->matches);

            if ($previousMatchDay !== null) {
                self::assertGreaterThan($previousMatchDay->number, $matchDay->number);
            }

            $previousMatchDay = $matchDay;
        }

        self::assertSame(count($dates) * count(self::$teamIds) / 2, $matchCount);

        return $season->id;
    }

    /**
     * @param string $seasonId
     * @return string
     */
    #[Depends("testSeasonCanBeStarted")]
    public function testMatchesCanBeScheduledPerMatchDay(string $seasonId): string
    {
        $timeZone = new DateTimeZone('Europe/Berlin');
        self::assertTimeZoneUsesDaylightSavingTime($timeZone);
        $appointments = $this->createMatchAppointments($timeZone);
        $validKickoffTimes = [];
        $kickoffComparisonFormat = 'D,H:i:s';
        foreach ($appointments as $appointment) {
            $kickoff = self::parseDateTime($appointment['kickoff'])->setTimezone($timeZone);
            $validKickoffTimes[] = $kickoff->format($kickoffComparisonFormat);
        }

        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        foreach ($season->match_days as $matchDay) {
            $this->client->scheduleAllMatchesForMatchDay($matchDay->id, $appointments);

            foreach ($matchDay->matches as $match) {
                $match = $this->client->getMatchById($match->id);

                self::assertNotNull($match);
                self::assertNotNull($match->pitch);
                self::assertNotNull($match->kickoff);

                // Make sure that kickoff matches one of the appointments
                $match->kickoff = self::parseDateTime($match->kickoff)->setTimezone($timeZone);
                self::assertContains($match->kickoff->format($kickoffComparisonFormat), $validKickoffTimes);
            }
        }

        return $seasonId;
    }

    /**
     * @param string $seasonId
     * @return string
     */
    #[Depends("testMatchesCanBeScheduledPerMatchDay")]
    public function testAllMatchesCanBeScheduledAtOnce(string $seasonId): string
    {
        $timeZone = new DateTimeZone('Europe/Berlin');
        self::assertTimeZoneUsesDaylightSavingTime($timeZone);
        $appointments = $this->createMatchAppointments($timeZone);
        $validKickoffTimes = [];
        $kickoffComparisonFormat = 'D,H:i:s';
        foreach ($appointments as $appointment) {
            $kickoff = self::parseDateTime($appointment['kickoff'])->setTimezone($timeZone);
            $validKickoffTimes[] = $kickoff->format($kickoffComparisonFormat);
        }

        $this->client->scheduleAllMatchesForSeason($seasonId, $appointments);

        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        foreach ($season->match_days as $matchDay) {
            foreach ($matchDay->matches as $match) {
                $match = $this->client->getMatchById($match->id);

                self::assertNotNull($match);
                self::assertNotNull($match->pitch);
                self::assertNotNull($match->kickoff);

                // Make sure that kickoff matches one of the appointments
                $match->kickoff = self::parseDateTime($match->kickoff)->setTimezone($timeZone);
                self::assertContains($match->kickoff->format($kickoffComparisonFormat), $validKickoffTimes);
            }
        }

        return $seasonId;
    }

    /**
     * @param string $seasonId
     * @return string
     */
    #[Depends("testAllMatchesCanBeScheduledAtOnce")]
    public function testMatchesCanBeQueriedByKickoff(string $seasonId): string
    {
        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);

        $minDate = null;
        $maxDate = null;

        foreach ($season->match_days as $matchDay) {
            $startDate = new DateTimeImmutable($matchDay->start_date);
            $endDate = new DateTimeImmutable($matchDay->end_date);

            if ($minDate === null || $startDate < $minDate) {
                $minDate = $startDate;
            }

            if ($maxDate === null || $endDate > $maxDate) {
                $maxDate = $endDate;
            }
        }

        $minDate = $minDate->modify('+ 7 days')->setTime(0, 0, 0);
        $maxDate = $maxDate->modify('- 7 days')->setTime(23, 59, 59);

        self::assertTrue($minDate < $maxDate);

        $matches = $this->client->getMatchesByKickoff($minDate->format(DATE_ATOM), $maxDate->format(DATE_ATOM));

        $matches = array_filter($matches, function ($match) {
            return $match->kickoff !== null;
        });

        self::assertNotEmpty($matches);

        foreach ($matches as $match) {
            $kickoff = new DateTimeImmutable($match->kickoff);

            self::assertGreaterThanOrEqual($minDate, $kickoff);
            self::assertLessThanOrEqual($maxDate, $kickoff);
        }

        return $seasonId;
    }

    /**
     * @param string $seasonId
     * @return string
     */
    #[Depends("testMatchesCanBeQueriedByKickoff")]
    public function testMatchCanBeScheduledToAnotherMatchDay(string $seasonId): string
    {
        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        $matchA = $season->match_days[0]->matches[0];
        $matchB = $season->match_days[1]->matches[0];

        self::assertNotNull($matchA);
        self::assertNotNull($matchB);

        $this->client->scheduleMatch($matchA->id, null, $season->match_days[1]->id);
        $this->client->scheduleMatch($matchB->id, null, $season->match_days[0]->id);

        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);

        self::assertArrayContainsObjectWithAttribute($season->match_days[1]->matches, 'id', $matchA->id);
        self::assertArrayContainsObjectWithAttribute($season->match_days[0]->matches, 'id', $matchB->id);

        return $seasonId;
    }

    /**
     * @param string $seasonId
     * @return string
     */
    #[Depends("testMatchCanBeScheduledToAnotherMatchDay")]
    public function testMatchCanBeScheduledWithKickoff(string $seasonId): string
    {
        $season = $this->client->getSeasonByIdWithMatchDays($seasonId);
        $matchId = $season->match_days[0]->matches[0]->id;
        $match = $this->client->getMatchById($matchId);

        self::assertNotNull($match);

        $requestedKickoff = self::parseDateTime('2024-10-05T11:23:44+02:00');
        self::assertNotEquals($requestedKickoff->getTimestamp(), self::parseDateTime($match->kickoff)->getTimestamp());

        $this->client->scheduleMatch($matchId, self::formatDateTime($requestedKickoff), null);

        $match = $this->client->getMatchById($matchId);
        self::assertNotNull($match);
        self::assertNotNull($match->kickoff);
        self::assertSame($requestedKickoff->getTimestamp(), self::parseDateTime($match->kickoff)->getTimestamp());

        return $matchId;
    }

    /**
     * @param string $matchId
     * @return string
     */
    #[Depends("testMatchCanBeScheduledWithKickoff")]
    public function testMatchCanBeLocated(string $matchId): string
    {
        $match = $this->client->getMatchById($matchId);

        self::assertNotNull($match);

        $pitchId = IdGenerator::generate();
        $this->client->createPitch($pitchId, 'Pitch ABC', 12.34, 23.45);
        $this->client->locateMatch($matchId, $pitchId);

        $match = $this->client->getMatchById($matchId);
        self::assertNotNull($match);
        self::assertNotNull($match->pitch);
        self::assertSame($pitchId, $match->pitch->id);

        return $matchId;
    }

    /**
     * @param string $matchId
     */
    #[Depends("testMatchCanBeLocated")]
    public function testDeletingUsedPitchFails(string $matchId): void
    {
        $match = $this->client->getMatchById($matchId);
        self::assertNotNull($match);
        self::assertNotNull($match->pitch);

        $this->expectClientException();
        $this->client->deletePitch($match->pitch->id);
    }

    /**
     * @param string $seasonId
     */
    #[Depends("testSeasonCanBeStarted")]
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
     * @param string $seasonId
     * @return string
     */
    #[Depends("testSeasonCanBeStarted")]
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
        $updatedAt = self::parseDateTime($season->ranking->updated_at)->getTimestamp();
        self::assertLessThan(5, $now - $updatedAt);

        return $seasonId;
    }

    /**
     * @param string $seasonId
     */
    #[Depends("testSubmittingMatchResultAffectsRanking")]
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
     * @param string $seasonId
     * @return string
     */
    #[Depends("testSubmittingMatchResultAffectsRanking")]
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
        $updatedAt = self::parseDateTime($season->ranking->updated_at)->getTimestamp();
        self::assertLessThan(5, $now - $updatedAt);

        return $seasonId;
    }

    /**
     * @param string $seasonId
     * @return string
     */
    #[Depends("testCancellingMatchAffectsRanking")]
    #[Depends("testCancellingMatchByNonParticipatingTeamFails")]
    public function testPenaltiesAffectRanking(string $seasonId): string
    {
        $penaltyId = IdGenerator::generate();
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
     * @param string $seasonId
     * @return string
     */
    #[Depends("testPenaltiesAffectRanking")]
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
        self::assertEquals($newStart->getTimestamp(), self::parseDateTime($matchDay->start_date)->getTimestamp());
        self::assertEquals($newEnd->getTimestamp(), self::parseDateTime($matchDay->end_date)->getTimestamp());

        return $seasonId;
    }

    /**
     * @param string $seasonId
     * @return string
     */
    #[Depends("testMatchDayCanBeRescheduled")]
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
     * @param string $seasonId
     */
    #[Depends("testTeamCanBeReplacedWhileSeasonInProgress")]
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
