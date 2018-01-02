<?php
/**
 * MatchFactory.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Domain;

/**
 * A factory which constructs Match domain objects and implements a match day generation algorithm
 */
class MatchFactory extends EntityFactory
{
    /**
     * @param Season $season
     * @param int    $matchDay
     * @param Team   $homeTeam
     * @param Team   $guestTeam
     * @return Match
     */
    public function createMatch(Season $season, int $matchDay, Team $homeTeam, Team $guestTeam) : Match
    {
        return new Match($this->getIdGenerator(), $season, $matchDay, $homeTeam, $guestTeam);
    }

    /**
     * Implements a match day generation algorithm
     *
     * @link https://de.wikipedia.org/wiki/Spielplan_(Sport)
     *
     * @param Season $season
     * @return Match[]
     * @throws DomainException If some unforeseen algorithm error occurs
     */
    public function createMatchesForSeason(Season $season) : array
    {
        $shuffledTeams = array_values($season->getTeams());
        shuffle($shuffledTeams);
        if (count($shuffledTeams) % 2 != 0) {
            $shuffledTeams[] = null;
        }
        $teamCount = count($shuffledTeams);
        $matchDaysPerHalf = $teamCount - 1;

        $matchDays = [];
        $allMatches = [];
        for ($n = 1; $n <= $matchDaysPerHalf; $n++) {
            $matchDays[$n] = $this->generateMatchDay($n, $shuffledTeams, $season);
            $rematchDay = $n+$matchDaysPerHalf;
            $matchDays[$rematchDay] = $this->generateRematches($matchDays[$n], $rematchDay);
            $allMatches = array_merge($allMatches, $matchDays[$n], $matchDays[$rematchDay]);
        }

        return $allMatches;
    }

    /**
     * @param array $matches
     * @param int   $matchDay
     * @return Match[]
     */
    private function generateRematches(array $matches, int $matchDay) : array
    {
        $rematches = [];
        foreach ($matches as $match) {
            $rematches[] = $match->rematch($this->getIdGenerator(), $matchDay);
        }
        return $rematches;
    }

    /**
     * Implements a match day generation algorithm
     *
     * @link https://de.wikipedia.org/wiki/Spielplan_(Sport)
     *
     * @param int    $matchDay
     * @param array  $teams    0-based array of teams
     * @param Season $season
     * @return Match[]
     * @throws DomainException If some unforeseen algorithm error occurs
     */
    private function generateMatchDay(int $matchDay, array $teams, Season $season) : array
    {
        $matchList = [];
        $matchDayCount = count($teams) - 1;
        $teamCount = count($teams);
        for ($k = 1; $k < $teamCount; $k++) {
            for ($l = 1; $l < $k; $l++) {
                if (($k + $l) % $matchDayCount == ($matchDay % $matchDayCount)) {
                    $sumIsEven = (($k + $l) % 2 == 0);
                    $homeTeam = $sumIsEven ? $teams[$k-1] : $teams[$l-1];
                    $guestTeam = $sumIsEven ? $teams[$l-1] : $teams[$k-1];
                    if (null !== $homeTeam && null !== $guestTeam) {
                        $matchList[] = $this->createMatch($season, $matchDay, $homeTeam, $guestTeam);
                    }
                    unset($teams[$k-1]);
                    unset($teams[$l-1]);
                }
            }
        }

        if (count($teams) != 2) {
            throw new DomainException(sprintf(
                'Matchday algorithm failed: Expected 2 teams left. Actual: %d teams',
                count($teams)
            ));
        }

        $k = max(array_keys($teams));
        $l = min(array_keys($teams));
        $homeTeam = $l+1 > $matchDayCount/2 ? $teams[$k] : $teams[$l];
        $guestTeam = $l+1 > $matchDayCount/2 ? $teams[$l] : $teams[$k];
        if (null !== $homeTeam && null !== $guestTeam) {
            $matchList[] = $this->createMatch($season, $matchDay, $homeTeam, $guestTeam);
        }

        return $matchList;
    }
}