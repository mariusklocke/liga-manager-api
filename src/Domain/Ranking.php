<?php

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;

class Ranking
{
    /** @var Season */
    private $season;

    /** @var DateTimeImmutable */
    private $updatedAt;

    /** @var CollectionInterface|RankingPosition[] */
    private $positions;

    public function __construct(Season $season, callable $collectionFactory)
    {
        $this->season = $season;
        $this->positions = $collectionFactory();
        foreach ($season->getTeams() as $team) {
            $this->positions[$team->getId()] = new RankingPosition($this, $team);
        }
    }

    /**
     * @param string $homeTeamId
     * @param string $guestTeamId
     * @param MatchResult $matchResult
     * @throws DomainException If a given team id does not have a ranking
     */
    public function addResult(string $homeTeamId, string $guestTeamId, MatchResult $matchResult)
    {
        if (!isset($this->positions[$homeTeamId])) {
            throw new DomainException(sprintf('Home Team "%s" is not ranked', $homeTeamId));
        }
        if (!isset($this->positions[$guestTeamId])) {
            throw new DomainException(sprintf('Guest Team "%s" is not ranked', $guestTeamId));
        }
        $this->positions[$homeTeamId]->addResult($matchResult->getHomeScore(), $matchResult->getGuestScore());
        $this->positions[$guestTeamId]->addResult($matchResult->getGuestScore(), $matchResult->getHomeScore());
        $this->reorder();
    }

    /**
     * Reorders the ranking positions
     */
    private function reorder()
    {
        /** @var RankingPosition[] $sortedArray */
        $sortedArray = $this->positions->toArray();
        uasort($sortedArray, function(RankingPosition $p1, RankingPosition $p2) {
            return $p2->compare($p1);
        });
        $index = 1;
        /** @var RankingPosition $previous */
        $previous = null;
        foreach ($sortedArray as $position) {
            if (null !== $previous && $position->compare($previous) === RankingPosition::COMPARISON_EQUAL) {
                $position->setNumber($previous->getNumber());
            } else {
                $position->setNumber($index);
            }
            $position->setSortIndex($index);
            $index++;
            $previous = $position;
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @return string
     */
    public function toString()
    {
        $parts = [];
        foreach ($this->positions as $teamId => $position) {
            /** @var $position RankingPosition */
            $parts[] = $position->toString();
        }
        return implode(PHP_EOL, $parts);
    }
}
