<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\Value\DatePeriod;

class RescheduleMatchDayCommand implements CommandInterface
{
    /** @var string */
    private $matchDayId;

    /** @var DatePeriod */
    private $datePeriod;

    /**
     * @param string $matchDayId
     * @param DatePeriod $datePeriod
     */
    public function __construct(string $matchDayId, DatePeriod $datePeriod)
    {
        $this->matchDayId = $matchDayId;
        $this->datePeriod = $datePeriod;
    }

    /**
     * @return string
     */
    public function getMatchDayId(): string
    {
        return $this->matchDayId;
    }

    /**
     * @return DatePeriod
     */
    public function getDatePeriod(): DatePeriod
    {
        return $this->datePeriod;
    }
}
