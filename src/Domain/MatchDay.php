<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Exception\ConflictException;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Value\MatchResult;

class MatchDay extends Entity
{
    /** @var Season|null */
    private ?Season $season = null;

    /** @var Tournament|null */
    private ?Tournament $tournament = null;

    /** @var int */
    private int $number;

    /** @var DateTimeImmutable */
    private DateTimeImmutable $startDate;

    /** @var DateTimeImmutable */
    private DateTimeImmutable $endDate;

    /** @var Collection|MatchEntity[] */
    private Collection $matches;

    /**
     * @param string|null $id
     * @param Competition $competition
     * @param int $number
     * @param DateTimeImmutable $startDate
     * @param DateTimeImmutable $endDate
     */
    public function __construct(?string $id, Competition $competition, int $number, DateTimeImmutable $startDate, DateTimeImmutable $endDate)
    {
        parent::__construct($id);
        Assert::true(
            $startDate <= $endDate,
            'Invalid date range: Start date cannot be after end date',
            InvalidInputException::class
        );
        $this->setCompetition($competition);
        $this->number = $number;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->matches = new ArrayCollection();
    }

    /**
     * @param string|null $id
     * @param Team $homeTeam
     * @param Team $guestTeam
     */
    public function createMatch(?string $id, Team $homeTeam, Team $guestTeam): void
    {
        $match = new MatchEntity($id, $this, $homeTeam, $guestTeam);
        $this->matches[] = $match;
    }

    /**
     * @return Competition
     */
    public function getCompetition(): Competition
    {
        return $this->season ?? $this->tournament;
    }

    /**
     * @return MatchEntity[]
     */
    public function getMatches(): array
    {
        return $this->matches->toArray();
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * Clears all matches
     */
    public function clearMatches(): void
    {
        Assert::false(
            $this->hasMatchWithResult(),
            'Cannot clear matches with result. Failed for matchDay ' . $this->number,
            ConflictException::class
        );
        $this->matches->clear();
    }

    /**
     * @param DateTimeImmutable $startDate
     * @param DateTimeImmutable $endDate
     */
    public function reschedule(DateTimeImmutable $startDate, DateTimeImmutable $endDate): void
    {
        Assert::true(
            $startDate <= $endDate,
            'Invalid date range: Start date cannot be after end date',
            InvalidInputException::class
        );
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * @param string $homeTeamId
     * @param string $guestTeamId
     * @param MatchResult $matchResult
     */
    public function addResult(string $homeTeamId, string $guestTeamId, MatchResult $matchResult): void
    {
        if ($this->season !== null) {
            $this->season->getRanking()->addResult($homeTeamId, $guestTeamId, $matchResult);
        }
    }

    /**
     * @param string $homeTeamId
     * @param string $guestTeamId
     * @param MatchResult $matchResult
     */
    public function revertResult(string $homeTeamId, string $guestTeamId, MatchResult $matchResult): void
    {
        if ($this->season !== null) {
            $this->season->getRanking()->revertResult($homeTeamId, $guestTeamId, $matchResult);
        }
    }

    /**
     * @return bool
     */
    private function hasMatchWithResult(): bool
    {
        foreach ($this->matches as $match) {
            if ($match->hasResult()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Competition $competition
     */
    private function setCompetition(Competition $competition): void
    {
        if ($competition instanceof Season) {
            $this->season = $competition;
        } elseif ($competition instanceof Tournament) {
            $this->tournament = $competition;
        }
    }
}
