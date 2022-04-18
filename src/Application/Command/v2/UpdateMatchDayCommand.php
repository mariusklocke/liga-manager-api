<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

use HexagonalPlayground\Domain\Value\DatePeriod;

class UpdateMatchDayCommand extends UpdateCommand implements CommandInterface
{
    private DatePeriod $datePeriod;

    /**
     * @param string $id
     * @param DatePeriod $datePeriod
     */
    public function __construct(string $id, DatePeriod $datePeriod)
    {
        $this->id = $id;
        $this->datePeriod = $datePeriod;
    }

    /**
     * @return DatePeriod
     */
    public function getDatePeriod(): DatePeriod
    {
        return $this->datePeriod;
    }
}
