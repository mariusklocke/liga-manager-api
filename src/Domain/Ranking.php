<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Exception\ConflictException;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Domain\Exception\UniquenessException;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Value\MatchResult;

class Ranking
{
    /** @var Season */
    private Season $season;

    /** @var DateTimeImmutable|null */
    private ?DateTimeImmutable $updatedAt = null;

    /** @var Collection */
    private Collection $positions;

    /** @var Collection */
    private Collection $penalties;

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
    public function addResult(string $homeTeamId, string $guestTeamId, MatchResult $matchResult): void
    {
        Assert::true(
            $this->season->isInProgress(),
            'Cannot add a result to a season which is not in progress',
            ConflictException::class
        );

        $this->getPositionForTeam($homeTeamId)->addResult($matchResult->getHomeScore(), $matchResult->getGuestScore());
        $this->getPositionForTeam($guestTeamId)->addResult($matchResult->getGuestScore(), $matchResult->getHomeScore());
        $this->reorder();
    }

    /**
     * @param string $homeTeamId
     * @param string $guestTeamId
     * @param MatchResult $matchResult
     */
    public function revertResult(string $homeTeamId, string $guestTeamId, MatchResult $matchResult): void
    {
        Assert::true(
            $this->season->isInProgress(),
            'Cannot revert a result from a season which is not in progress',
            ConflictException::class
        );
        $this->getPositionForTeam($homeTeamId)->revertResult($matchResult->getHomeScore(), $matchResult->getGuestScore());
        $this->getPositionForTeam($guestTeamId)->revertResult($matchResult->getGuestScore(), $matchResult->getHomeScore());
        $this->reorder();
    }

    /**
     * @param RankingPenalty $penalty
     */
    public function addPenalty(RankingPenalty $penalty): void
    {
        Assert::true(
            $this->season->isInProgress(),
            'Cannot add a penalty to season which is not in progress',
            ConflictException::class
        );
        Assert::true(
            $this->getPenalty($penalty->getId()) === null,
            sprintf('Ranking penalty with ID %s already exists', $penalty->getId()),
            UniquenessException::class
        );
        $this->penalties[$penalty->getId()] = $penalty;
        $this->getPositionForTeam($penalty->getTeam()->getId())->subtractPoints($penalty->getPoints());
        $this->reorder();
    }

    /**
     * @param string $penaltyId
     * @return RankingPenalty|null
     */
    public function getPenalty(string $penaltyId): ?RankingPenalty
    {
        return $this->penalties[$penaltyId] ?? null;
    }

    /**
     * @param RankingPenalty $penalty
     */
    public function removePenalty(RankingPenalty $penalty): void
    {
        Assert::true(
            $this->season->isInProgress(),
            'Cannot remove a penalty from a season which is not in progress',
            ConflictException::class
        );
        Assert::true(
            $this->getPenalty($penalty->getId()) !== null,
            sprintf('Ranking penalty with ID %s does not exist', $penalty->getId()),
            NotFoundException::class
        );

        $this->getPositionForTeam($penalty->getTeam()->getId())->addPoints($penalty->getPoints());
        $this->penalties->removeElement($penalty);
        $this->reorder();
    }

    /**
     * Reorders the ranking positions
     */
    private function reorder(): void
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

    public function replaceTeam(Team $from, Team $to): void
    {
        $rankingPosition = $this->getPositionForTeam($from->getId());
        $rankingPosition->setTeam($to);

        foreach ($this->penalties as $penalty) {
            if ($penalty->getTeam()->equals($from)) {
                $penalty->setTeam($to);
            }
        }
    }

    /**
     * @param string $teamId
     * @return RankingPosition
     */
    private function getPositionForTeam(string $teamId): RankingPosition
    {
        Assert::true(
            isset($this->positions[$teamId]),
            sprintf('Team %s is not ranked', $teamId),
            NotFoundException::class
        );

        return $this->positions[$teamId];
    }
}
