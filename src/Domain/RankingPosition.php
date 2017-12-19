<?php

namespace HexagonalDream\Domain;

class RankingPosition
{
    const COMPARISON_INFERIOR = -1;
    const COMPARISON_EQUAL = 0;
    const COMPARISON_SUPERIOR = 1;

    /** @var Team */
    private $team;

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

    public function __construct(Team $team)
    {
        $this->team = $team;
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
     * @return Team
     */
    public function getTeam() : Team
    {
        return $this->team;
    }

    /**
     * @param int $number
     * @return RankingPosition
     */
    public function setNumber(int $number) : RankingPosition
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumber() : int
    {
        return $this->number;
    }

    public function toString() : string
    {
        return sprintf('%d. %s %d %d', $this->number, $this->getTeam()->getName(), $this->getGoalDifference(), $this->points);
    }

    /**
     * @return int
     */
    private function getGoalDifference() : int
    {
        return $this->scoredGoals - $this->concededGoals;
    }
}
