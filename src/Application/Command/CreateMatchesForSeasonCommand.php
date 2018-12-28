<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\InputParser;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Application\Value\DatePeriod;

class CreateMatchesForSeasonCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $seasonId;

    /** @var array|DatePeriod[] */
    private $matchDaysDates;

    /**
     * @param string $seasonId
     * @param array  $dates
     */
    public function __construct($seasonId, $dates)
    {
        TypeAssert::assertString($seasonId, 'seasonId');
        TypeAssert::assertArray($dates, 'dates');

        $this->seasonId = $seasonId;
        $this->matchDaysDates = [];
        foreach ($dates as $index => $datePeriod) {
            TypeAssert::assertArray($datePeriod, 'dates[' . $index . ']');
            $this->matchDaysDates[] = InputParser::parseDatePeriod($datePeriod);
        }
    }

    /**
     * @return string
     */
    public function getSeasonId(): string
    {
        return $this->seasonId;
    }

    /**
     * @return array|DatePeriod[]
     */
    public function getMatchDaysDates(): array
    {
        return $this->matchDaysDates;
    }
}
