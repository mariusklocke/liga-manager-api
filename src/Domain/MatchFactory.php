<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateInterval;
use DateTimeImmutable;
use Generator;
use UnexpectedValueException;

/**
 * A factory which constructs Match objects and implements a match day generation algorithm
 */
class MatchFactory
{
    /**
     * Create all matches for a given season (including rematches)
     *
     * @param Season            $season
     * @param DateTimeImmutable $startAt
     * @return MatchDay[]
     */
    public function createMatchDaysForSeason(Season $season, DateTimeImmutable $startAt) : array
    {
        $teams = array_values($season->getTeams());
        if (count($teams) % 2 != 0) {
            $teams[] = null;
        }
        shuffle($teams);
        $matchDaysPerHalf = count($teams) - 1;

        $matchDays = [];
        $matchPlannedFor = $startAt;
        for ($matchDayNumber = 1; $matchDayNumber <= $matchDaysPerHalf; $matchDayNumber++) {
            $matchDay = new MatchDay($season, $matchDayNumber, $matchPlannedFor, $matchPlannedFor);
            $this->generateMatchesForMatchDay($matchDay, $teams, $matchPlannedFor);
            $matchDays[] = $matchDay;
            $matchPlannedFor = $matchPlannedFor->add(new DateInterval('P1W'));
        }

        return $matchDays;
    }

    /**
     * Generates a set of matches and adds them to a given MatchDay
     *
     * Implements an algorithm found on Wikipedia
     * @link https://de.wikipedia.org/wiki/Spielplan_(Sport)
     *
     * @param MatchDay $matchDay
     * @param array $teams 0-based array of teams
     * @param DateTimeImmutable $plannedFor
     */
    private function generateMatchesForMatchDay(MatchDay $matchDay, array $teams, DateTimeImmutable $plannedFor) : void
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
                        $matchDay->addMatch(new Match($matchDay, $homeTeam, $guestTeam, $plannedFor));
                    }
                    unset($teams[$k-1]);
                    unset($teams[$l-1]);
                }
            }
        }

        if (count($teams) != 2) {
            // This should never happen, but a check doesn't hurt and a potential algorithmic flaw can be found early
            throw new UnexpectedValueException(sprintf(
                'MatchDay generation algorithm failed: Expected 2 teams left. Actual: %d teams',
                count($teams)
            ));
        }

        $k = max(array_keys($teams));
        $l = min(array_keys($teams));
        $homeTeam = $l+1 > $matchDayCount/2 ? $teams[$k] : $teams[$l];
        $guestTeam = $l+1 > $matchDayCount/2 ? $teams[$l] : $teams[$k];
        if (null !== $homeTeam && null !== $guestTeam) {
            $matchDay->addMatch(new Match($matchDay, $homeTeam, $guestTeam, $plannedFor));
        }
    }
}