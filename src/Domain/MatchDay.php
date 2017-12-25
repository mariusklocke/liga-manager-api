<?php

namespace HexagonalDream\Domain;

class MatchDay
{
    /** @var Season */
    private $season;

    /** @var int */
    private $number;

    /** @var Match[] */
    private $matches;

    public function __construct(Season $season, int $number)
    {
        $this->season = $season;
        $this->number = $number;
        $this->matches = [];
    }

    /**
     * @param Match $match
     * @return MatchDay
     */
    public function addMatch(Match $match) : MatchDay
    {
        $this->matches[] = $match;
        return $this;
    }

    /**
     * @return Match[]
     */
    public function getMatches() : array
    {
        return $this->matches;
    }

    public function toString() : string
    {
        return sprintf(
            "Matchday No. %d" . PHP_EOL . "%s" . PHP_EOL,
            $this->number,
            implode(PHP_EOL, array_map(function(Match $match) {
                return $match->toString();
            }, $this->matches))
        );
    }
}
