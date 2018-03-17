<?php
declare(strict_types=1);

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

    /**
     * @param Season $season
     * @param CollectionInterface $positions
     */
    public function __construct(Season $season, $positions)
    {
        $this->season = $season;
        $this->positions = $positions;
        $this->positions->clear();
        foreach ($season->getTeams() as $team) {
            $this->positions[$team->getId()] = new RankingPosition($this, $team);
        }
    }

    /**
     * @param string $homeTeamId
     * @param string $guestTeamId
     * @param MatchResult $matchResult
     */
    public function addResult(string $homeTeamId, string $guestTeamId, MatchResult $matchResult)
    {
        $this->getPositionForTeam($homeTeamId)->addResult($matchResult->getHomeScore(), $matchResult->getGuestScore());
        $this->getPositionForTeam($guestTeamId)->addResult($matchResult->getGuestScore(), $matchResult->getHomeScore());
        $this->reorder();
    }

    /**
     * @param string $homeTeamId
     * @param string $guestTeamId
     * @param MatchResult $matchResult
     */
    public function revertResult(string $homeTeamId, string $guestTeamId, MatchResult $matchResult)
    {
        $this->getPositionForTeam($homeTeamId)->revertResult($matchResult->getHomeScore(), $matchResult->getGuestScore());
        $this->getPositionForTeam($guestTeamId)->revertResult($matchResult->getGuestScore(), $matchResult->getHomeScore());
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

    /**
     * @param string $teamId
     * @return RankingPosition
     * @throws DomainException If a given team id does not have a RankingPosition
     */
    private function getPositionForTeam(string $teamId)
    {
        if (!isset($this->positions[$teamId])) {
            throw new DomainException(sprintf('Team "%s" is not ranked', $teamId));
        }
        return $this->positions[$teamId];
    }
}
