<?php

namespace HexagonalDream\Domain;

class RankingPosition implements \Serializable
{
    const COMPARISON_INFERIOR = -1;
    const COMPARISON_EQUAL = 0;
    const COMPARISON_SUPERIOR = 1;

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

    public function __construct()
    {
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

    public function toString(string $teamName) : string
    {
        return sprintf('%d. %s %d %d', $this->number, $teamName, $this->getGoalDifference(), $this->points);
    }

    /**
     * @return int
     */
    private function getGoalDifference() : int
    {
        return $this->scoredGoals - $this->concededGoals;
    }

    /**
     * String representation of object
     * @link  http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([
            'number' => $this->number,
            'matches' => $this->matches,
            'wins' => $this->wins,
            'draws' => $this->draws,
            'losses' => $this->losses,
            'scoredGoals' => $this->scoredGoals,
            'concededGoals' => $this->concededGoals,
            'points' => $this->points
        ]);
    }

    /**
     * Constructs the object
     * @link  http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $array = unserialize($serialized);
        $this->number = $array['number'];
        $this->matches = $array['matches'];
        $this->wins = $array['wins'];
        $this->draws = $array['draws'];
        $this->losses = $array['losses'];
        $this->scoredGoals = $array['scoredGoals'];
        $this->concededGoals = $array['concededGoals'];
        $this->points = $array['points'];
    }
}
