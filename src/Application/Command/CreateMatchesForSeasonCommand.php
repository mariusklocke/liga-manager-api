<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\Value\DatePeriod;
use HexagonalPlayground\Application\Exception\InvalidInputException;

class CreateMatchesForSeasonCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $seasonId;

    /** @var array|DatePeriod[] */
    private $matchDaysDates;

    /**
     * CreateMatchesForSeasonCommand constructor.
     * @param string $seasonId
     * @param iterable|DatePeriod[] $matchDaysDates
     */
    public function __construct(string $seasonId, iterable $matchDaysDates)
    {
        $this->seasonId = $seasonId;
        $this->matchDaysDates = [];
        foreach ($matchDaysDates as $matchDaysDate) {
            if (!($matchDaysDate instanceof DatePeriod)) {
                throw new InvalidInputException('MatchDay dates have to be instances of ' . DatePeriod::class);
            }
            $this->matchDaysDates[] = $matchDaysDate;
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
