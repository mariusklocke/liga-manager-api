<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Import;

class L98MatchDayModel
{
    /** @var int */
    private $number;

    /** @var \DateTimeImmutable */
    private $startDate;

    /** @var \DateTimeImmutable */
    private $endDate;

    /** @var L98MatchModel[] */
    private $matches;

    /**
     * @param int $number
     * @param \DateTimeImmutable $startDate
     * @param \DateTimeImmutable $endDate
     */
    public function __construct(int $number, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        $this->number = $number;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->matches = [];
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * @param L98MatchModel $match
     */
    public function addMatch(L98MatchModel $match): void
    {
        $this->matches[] = $match;
    }

    /**
     * @return array
     */
    public function getMatches(): array
    {
        return $this->matches;
    }
}