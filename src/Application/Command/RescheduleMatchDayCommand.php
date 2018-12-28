<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\InputParser;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Application\Value\DatePeriod;

class RescheduleMatchDayCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $matchDayId;

    /** @var DatePeriod */
    private $datePeriod;

    /**
     * @param string $matchDayId
     * @param array $datePeriod
     */
    public function __construct($matchDayId, $datePeriod)
    {
        TypeAssert::assertString($matchDayId, 'matchDayId');
        TypeAssert::assertArray($datePeriod, 'datePeriod');
        $this->matchDayId = $matchDayId;
        $this->datePeriod = InputParser::parseDatePeriod($datePeriod);
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