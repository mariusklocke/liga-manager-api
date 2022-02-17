<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Value;

use DateTimeImmutable;

class DatePeriod extends ValueObject
{
    /** @var DateTimeImmutable */
    private DateTimeImmutable $startDate;

    /** @var DateTimeImmutable */
    private DateTimeImmutable $endDate;

    /**
     * @param DateTimeImmutable $startDate
     * @param DateTimeImmutable $endDate
     */
    public function __construct(DateTimeImmutable $startDate, DateTimeImmutable $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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
}