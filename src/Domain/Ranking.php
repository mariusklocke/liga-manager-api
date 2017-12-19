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
            $this->positions[] = new RankingPosition($team);
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
            $this
                ->getPositionByTeam($team)
                ->addResult($match->getScoredGoalsBy($team), $match->getConcededGoalsBy($team));
        }
        $this->updatePositions();
    }

    /**
     * @param Team $team
     * @return RankingPosition
     * @throws UnrankedTeamException
     */
    private function getPositionByTeam(Team $team)
    {
        foreach ($this->positions as $position) {
            if ($position->getTeam()->equals($team)) {
                return $position;
            }
        }

        throw new UnrankedTeamException();
    }

    private function updatePositions()
    {
        // Sort the RankingPositions from good/top to bad/bottom (descending in points)
        usort($this->positions, function(RankingPosition $p1, RankingPosition $p2) {
            return $p2->compare($p1);
        });

        // Generate ascending rank numbers
        $n = count($this->positions);
        $this->positions[0]->setNumber(1);
        $previous = $this->positions[0];
        for ($i = 1; $i < $n; $i++) {
            if ($this->positions[$i]->compare($previous) === RankingPosition::COMPARISON_EQUAL) {
                $this->positions[$i]->setNumber($previous->getNumber());
            } else {
                $this->positions[$i]->setNumber($i + 1);
            }
            $previous = $this->positions[$i];
        }
    }

    public function toString()
    {
        return implode(PHP_EOL, array_map(function(RankingPosition $position) {
            return $position->toString();
        }, $this->positions));
    }
}
