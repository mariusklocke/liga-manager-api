<?php

namespace HexagonalDream\Domain;

use HexagonalDream\Domain\Exception\UnrankedTeamException;
use HexagonalDream\Domain\Exception\TeamDidNotParticipateException;

class Ranking
{
    /** @var Season */
    private $season;

    /** @var RankingPosition[] */
    private $positions;

    public function __construct(Season $season)
    {
        $this->season = $season;
        $this->positions = [];
        foreach ($season->getTeams() as $team) {
            $this->positions[$team->getId()] = new RankingPosition();
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
        $this->sortPositions();
        $this->generatePositionNumbers();
    }

    /**
     * Generate ascending ranking numbers
     */
    private function generatePositionNumbers()
    {
        $rankNumber = 1;
        /** @var RankingPosition $previous */
        $previous = null;
        foreach ($this->positions as $position) {
            if (null !== $previous && $position->compare($previous) === RankingPosition::COMPARISON_EQUAL) {
                $position->setNumber($previous->getNumber());
            } else {
                $position->setNumber($rankNumber);
            }
            $rankNumber++;
            $previous = $position;
        }
    }

    /**
     * Sort the RankingPositions from good/top to bad/bottom (descending in points)
     */
    private function sortPositions()
    {
        uasort($this->positions, function(RankingPosition $p1, RankingPosition $p2) {
            return $p2->compare($p1);
        });
    }

    /**
     * @param string $teamId
     * @return string
     * @throws UnrankedTeamException
     */
    private function getTeamNameById(string $teamId)
    {
        foreach ($this->season->getTeams() as $team) {
            if ($team->getId() === $teamId) {
                return $team->getName();
            }
        }
        throw new UnrankedTeamException();
    }

    /**
     * @return string
     */
    public function toString()
    {
        $parts = [];
        foreach ($this->positions as $teamId => $position) {
            $parts[] = $position->toString($this->getTeamNameById($teamId));
        }
        return implode(PHP_EOL, $parts);
    }
}
