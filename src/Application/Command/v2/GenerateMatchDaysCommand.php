<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

use HexagonalPlayground\Domain\Value\DatePeriod;

class GenerateMatchDaysCommand implements CommandInterface
{
    /** @var string */
    private string $seasonId;

    /** @var DatePeriod[] */
    private array $matchDaysDates;

    /**
     * @param string       $seasonId
     * @param DatePeriod[] $dates
     */
    public function __construct(string $seasonId, array $dates)
    {
        $this->seasonId = $seasonId;
        $this->matchDaysDates = array_map(function (DatePeriod $datePeriod) {
            return $datePeriod;
        }, $dates);
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
