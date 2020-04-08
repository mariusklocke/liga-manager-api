<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Event\Publisher;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Value\MatchResult;

class Ranking
{
    /** @var Season */
    private $season;

    /** @var DateTimeImmutable|null */
    private $updatedAt;

    /** @var Collection */
    private $positions;

    /** @var Collection */
    private $penalties;

    /**
     * @param Season $season
     */
    public function __construct(Season $season)
    {
        $this->season = $season;
        $this->positions = new ArrayCollection();
        foreach ($season->getTeams() as $team) {
            $this->positions[$team->getId()] = new RankingPosition($this, $team);
        }
        $this->penalties = new ArrayCollection();
    }

    /**
     * @param string $homeTeamId
     * @param string $guestTeamId
     * @param MatchResult $matchResult
     */
    public function addResult(string $homeTeamId, string $guestTeamId, MatchResult $matchResult)
    {
        Assert::true($this->season->isInProgress(), 'Cannot add a result to a season which is not in progress');

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
        Assert::true($this->season->isInProgress(), 'Cannot revert a result from a season which is not in progress');

        $this->getPositionForTeam($homeTeamId)->revertResult($matchResult->getHomeScore(), $matchResult->getGuestScore());
        $this->getPositionForTeam($guestTeamId)->revertResult($matchResult->getGuestScore(), $matchResult->getHomeScore());
        $this->reorder();
    }

    /**
     * @param string $id
     * @param Team $team
     * @param string $reason
     * @param int $points
     * @param User $user
     */
    public function addPenalty(string $id, Team $team, string $reason, int $points, User $user): void
    {
        Assert::true($this->season->isInProgress(), 'Cannot add a penalty for a season which is not in progress');

        $penalty = new RankingPenalty($id, $this, $team, $reason, $points);
        $this->getPositionForTeam($team->getId())->subtractPoints($points);
        $this->penalties[$penalty->getId()] = $penalty;
        $this->reorder();

        Publisher::getInstance()->publish(new Event('ranking:penalty:added', [
            'seasonId'   => $this->season->getId(),
            'teamId'     => $team->getId(),
            'reason'     => $reason,
            'points'     => $points,
            'userId'     => $user->getId()
        ]));
    }

    /**
     * @param string $id
     * @param User $user
     */
    public function removePenalty(string $id, User $user): void
    {
        Assert::true($this->season->isInProgress(), 'Cannot remove a penalty from a season which is not in progress');

        /** @var RankingPenalty $penalty */
        $penalty = $this->penalties->get($id);
        Assert::false($penalty === null, 'Cannot find ranking penalty');

        $this->getPositionForTeam($penalty->getTeam()->getId())->addPoints($penalty->getPoints());
        $this->penalties->removeElement($penalty);
        $this->reorder();

        Publisher::getInstance()->publish(new Event('ranking:penalty:removed', [
            'seasonId'   => $this->season->getId(),
            'teamId'     => $penalty->getTeam()->getId(),
            'reason'     => $penalty->getReason(),
            'points'     => $penalty->getPoints(),
            'userId'     => $user->getId()
        ]));
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
        /** @var RankingPosition|null $previous */
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
     * @param string $teamId
     * @return RankingPosition
     */
    private function getPositionForTeam(string $teamId)
    {
        Assert::true(isset($this->positions[$teamId]), sprintf('Team %s is not ranked', $teamId));
        return $this->positions[$teamId];
    }
}
