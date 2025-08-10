<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use HexagonalPlayground\Domain\Exception\ConflictException;
use HexagonalPlayground\Domain\Value\DatePeriod;

/**
 * Generates MatchDays and Matches for a Season
 */
class MatchDayGenerator
{
    /**
     * Generates all MatchDays for a Season
     *
     * @param Season $season
     * @param array|DatePeriod[] $matchDayDates
     */
    public function generateMatchDaysForSeason(Season $season, array $matchDayDates): void
    {
        $teams = array_values($season->getTeams());
        count($teams) >= 2 || throw new ConflictException('teamCountTooLow', [2]);
        if (count($teams) % 2 != 0) {
            $teams[] = null;
        }
        shuffle($teams);

        $matchDaysPerHalf = count($teams) - 1;
        $possibleMatchDayCounts = [$matchDaysPerHalf, $matchDaysPerHalf * 2];

        in_array(count($matchDayDates), $possibleMatchDayCounts) || throw new ConflictException('matchDayCountMismatch', [implode(',', $possibleMatchDayCounts)]);

        /** @var DatePeriod[] $secondHalfMatchDayDates */
        $secondHalfMatchDayDates = [];
        if (count($matchDayDates) > $matchDaysPerHalf) {
            $secondHalfMatchDayDates = array_splice($matchDayDates, $matchDaysPerHalf, $matchDaysPerHalf);
        }

        $matchDayNumber = 1;
        foreach ($matchDayDates as $datePeriod) {
            $matchDay = $season->createMatchDay(null, $matchDayNumber, $datePeriod->getStartDate(), $datePeriod->getEndDate());
            $this->generateMatchesForMatchDay($matchDay, $teams);
            $matchDayNumber++;
        }

        if (!empty($secondHalfMatchDayDates)) {
            $i = 0;
            foreach ($season->getMatchDays() as $firstHalfMatchDay) {
                $secondHalfMatchDay = $season->createMatchDay(
                    null,
                    $matchDayNumber,
                    $secondHalfMatchDayDates[$i]->getStartDate(),
                    $secondHalfMatchDayDates[$i]->getEndDate()
                );
                foreach ($firstHalfMatchDay->getMatches() as $match) {
                    $secondHalfMatchDay->createMatch(null, $match->getGuestTeam(), $match->getHomeTeam());
                }
                $i++;
                $matchDayNumber++;
            }
        }
    }

    /**
     * Generates a set of matches and adds them to a given MatchDay
     *
     * Implements an algorithm found on Wikipedia
     * @link https://de.wikipedia.org/wiki/Spielplan_(Sport)
     *
     * @param MatchDay $matchDay
     * @param array $teams 0-based array of teams
     */
    private function generateMatchesForMatchDay(MatchDay $matchDay, array $teams) : void
    {
        $matchDayCount = count($teams) - 1;
        $teamCount = count($teams);
        for ($k = 1; $k < $teamCount; $k++) {
            for ($l = 1; $l < $k; $l++) {
                if (($k + $l) % $matchDayCount == ($matchDay->getNumber() % $matchDayCount)) {
                    $sumIsEven = (($k + $l) % 2 == 0);
                    $homeTeam = $sumIsEven ? $teams[$k-1] : $teams[$l-1];
                    $guestTeam = $sumIsEven ? $teams[$l-1] : $teams[$k-1];
                    if (null !== $homeTeam && null !== $guestTeam) {
                        $matchDay->createMatch(null, $homeTeam, $guestTeam);
                    }
                    unset($teams[$k-1]);
                    unset($teams[$l-1]);
                }
            }
        }

        $k = max(array_keys($teams));
        $l = min(array_keys($teams));
        $homeTeam = $l+1 > $matchDayCount/2 ? $teams[$k] : $teams[$l];
        $guestTeam = $l+1 > $matchDayCount/2 ? $teams[$l] : $teams[$k];
        if (null !== $homeTeam && null !== $guestTeam) {
            $matchDay->createMatch(null, $homeTeam, $guestTeam);
        }
    }
}
