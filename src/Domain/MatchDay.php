<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Util\Uuid;

class MatchDay
{
    /** @var string */
    private $id;

    /** @var Season|null */
    private $season;

    /** @var Tournament|null */
    private $tournament;

    /** @var int */
    private $number;

    /** @var DateTimeImmutable */
    private $startDate;

    /** @var DateTimeImmutable */
    private $endDate;

    /** @var Collection|Match[] */
    private $matches;

    /**
     * @param Competition $competition
     * @param int $number
     * @param DateTimeImmutable $startDate
     * @param DateTimeImmutable $endDate
     * @throws DomainException
     */
    public function __construct(Competition $competition, int $number, DateTimeImmutable $startDate, DateTimeImmutable $endDate)
    {
        if ($startDate > $endDate) {
            throw new DomainException('Invalid date range: Start date cannot be after end date');
        }

        $this->setCompetition($competition);
        $this->id = Uuid::create();
        $this->number = $number;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->matches = new ArrayCollection();
    }

    public function addMatch(Match $match): void
    {
        $this->matches[] = $match;
    }

    /**
     * @param Match $match
     * @return bool
     */
    public function hasMatch(Match $match): bool
    {
        return $this->matches->contains($match);
    }

    /**
     * @return Match[]
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
        $this->matches->clear();
    }

    /**
     * @return Competition
     */
    public function getCompetition(): Competition
    {
        return $this->season ?? $this->tournament;
    }

    /**
     * @param Competition $competition
     * @throws DomainException if $competition is an instance of unsupported type
     */
    private function setCompetition(Competition $competition): void
    {
        if ($competition instanceof Season) {
            $this->season = $competition;
        } elseif ($competition instanceof Tournament) {
            $this->tournament = $competition;
        } else {
            throw new DomainException('Unsupported competition type: ' . get_class($competition));
        }
    }
}