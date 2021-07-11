<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

class RankingPosition
{
    const COMPARISON_INFERIOR = -1;
    const COMPARISON_EQUAL = 0;
    const COMPARISON_SUPERIOR = 1;

    /** @var Ranking */
    private $ranking;

    /** @var Team */
    private $team;

    /** @var int */
    private $sortIndex;

    /** @var int */
    private $number;

    /** @var int */
    private $matches;

    /** @var int */
    private $wins;

    /** @var int */
    private $draws;

    /** @var int */
    private $losses;

    /** @var int */
    private $scoredGoals;

    /** @var int */
    private $concededGoals;

    /** @var int */
    private $points;

    public function __construct(Ranking $ranking, Team $team)
    {
        $this->ranking = $ranking;
        $this->team = $team;
        $this->sortIndex = 0;
        $this->number = 0;
        $this->matches = 0;
        $this->wins = 0;
        $this->draws = 0;
        $this->losses = 0;
        $this->scoredGoals = 0;
        $this->concededGoals = 0;
        $this->points = 0;
    }

    /**
     * @param int $scoredGoals
     * @param int $concededGoals
     */
    public function addResult(int $scoredGoals, int $concededGoals)
    {
        $this->scoredGoals += $scoredGoals;
        $this->concededGoals += $concededGoals;

        $this->matches++;
        if ($scoredGoals > $concededGoals) {
            $this->wins++;
            $this->points += 3;
            return;
        }
        if ($scoredGoals < $concededGoals) {
            $this->losses++;
            return;
        }

        $this->points += 1;
        $this->draws++;
    }

    /**
     * @param int $scoredGoals
     * @param int $concededGoals
     */
    public function revertResult(int $scoredGoals, int $concededGoals)
    {
        $this->scoredGoals -= $scoredGoals;
        $this->concededGoals -= $concededGoals;

        $this->matches--;
        if ($scoredGoals > $concededGoals) {
            $this->wins--;
            $this->points -= 3;
            return;
        }
        if ($scoredGoals < $concededGoals) {
            $this->losses--;
            return;
        }

        $this->points -= 1;
        $this->draws--;
    }

    public function subtractPoints(int $points): void
    {
        $this->points -= $points;
    }

    public function addPoints(int $points): void
    {
        $this->points += $points;
    }

    /**
     * @param RankingPosition $other
     * @return int
     */
    public function compare(RankingPosition $other)
    {
        // Point comparison
        if ($this->points != $other->points) {
            return ($this->points > $other->points) ? self::COMPARISON_SUPERIOR : self::COMPARISON_INFERIOR;
        }

        // Goal difference
        if ($this->getGoalDifference() != $other->getGoalDifference()) {
            return ($this->getGoalDifference() > $other->getGoalDifference()) ? self::COMPARISON_SUPERIOR : self::COMPARISON_INFERIOR;
        }

        return self::COMPARISON_EQUAL;
    }

    /**
     * @param int $number
     */
    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    /**
     * @param int $sortIndex
     */
    public function setSortIndex(int $sortIndex): void
    {
        $this->sortIndex = $sortIndex;
    }

    /**
     * @return int
     */
    public function getNumber() : int
    {
        return $this->number;
    }

    /**
     * @param Team $team
     */
    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    /**
     * @return int
     */
    private function getGoalDifference() : int
    {
        return $this->scoredGoals - $this->concededGoals;
    }
}
