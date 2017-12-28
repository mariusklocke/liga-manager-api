<?php

namespace HexagonalDream\Domain;

use DateTimeImmutable;
use HexagonalDream\Domain\Exception\UnrankedTeamException;
use HexagonalDream\Domain\Exception\TeamDidNotParticipateException;

class Ranking
{
    /** @var Season */
    private $season;

    /** @var DateTimeImmutable */
    private $updatedAt;

    /** @var CollectionInterface */
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
     * @param Match $match
     * @throws TeamDidNotParticipateException
     * @throws UnrankedTeamException
     */
    public function addResult(Match $match)
    {
        foreach ([$match->getHomeTeam(), $match->getGuestTeam()] as $team) {
            if (!isset($this->positions[$team->getId()])) {
                throw new UnrankedTeamException();
            }
            $this->positions[$team->getId()]->addResult($match->getScoredGoalsBy($team), $match->getConcededGoalsBy($team));
        }

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
