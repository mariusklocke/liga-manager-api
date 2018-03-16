<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Factory;

use Generator;
use HexagonalPlayground\Domain\Competition;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;
use UnexpectedValueException;

/**
 * A factory which constructs Match objects and implements a match day generation algorithm
 */
class MatchFactory extends EntityFactory
{
    /**
     * @param Competition $competition
     * @param int         $matchDay
     * @param Team        $homeTeam
     * @param Team        $guestTeam
     * @return Match
     */
    public function createMatch(Competition $competition, int $matchDay, Team $homeTeam, Team $guestTeam) : Match
    {
        return new Match($this->getIdGenerator()->generate(), $competition, $matchDay, $homeTeam, $guestTeam);
    }

    /**
     * Create all matches for a given season (including rematches)
     *
     * @param Season $season
     * @return Match[]
     */
    public function createMatchesForSeason(Season $season) : array
    {
        $teams = array_values($season->getTeams());
        if (count($teams) % 2 != 0) {
            $teams[] = null;
        }
        shuffle($teams);
        $matchDaysPerHalf = count($teams) - 1;

        $firstHalf = [];
        $secondHalf = [];
        for ($matchDay = 1; $matchDay <= $matchDaysPerHalf; $matchDay++) {
            foreach ($this->generateMatchDay($matchDay, $teams, $season) as $match) {
                /** @var Match $match */
                $firstHalf[] = $match;
                if ($season->hasSecondHalf()) {
                    $secondHalf[] = $match->rematch($this->getIdGenerator()->generate(), $matchDay + $matchDaysPerHalf);
                }
            }
        }

        return array_merge($firstHalf, $secondHalf);
    }

    /**
     * Generates a set of Match objects for a given MatchDay
     *
     * Implements an algorithm found on Wikipedia
     * @link https://de.wikipedia.org/wiki/Spielplan_(Sport)
     *
     * @param int    $matchDay
     * @param array  $teams    0-based array of teams
     * @param Season $season
     * @return Generator
     */
    private function generateMatchDay(int $matchDay, array $teams, Season $season) : Generator
    {
        $matchDayCount = count($teams) - 1;
        $teamCount = count($teams);
        for ($k = 1; $k < $teamCount; $k++) {
            for ($l = 1; $l < $k; $l++) {
                if (($k + $l) % $matchDayCount == ($matchDay % $matchDayCount)) {
                    $sumIsEven = (($k + $l) % 2 == 0);
                    $homeTeam = $sumIsEven ? $teams[$k-1] : $teams[$l-1];
                    $guestTeam = $sumIsEven ? $teams[$l-1] : $teams[$k-1];
                    if (null !== $homeTeam && null !== $guestTeam) {
                        yield $this->createMatch($season, $matchDay, $homeTeam, $guestTeam);
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
            yield $this->createMatch($season, $matchDay, $homeTeam, $guestTeam);
        }
    }
}