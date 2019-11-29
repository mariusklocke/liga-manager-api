<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\Value\DatePeriod;

class CreateMatchDayForSeasonCommand implements CommandInterface
{
    use AuthenticationAware;
    use IdAware;

    /** @var string */
    private $seasonId;

    /** @var int */
    private $number;

    /** @var DatePeriod */
    private $datePeriod;

    /**
     * @param string|null $id
     * @param string $seasonId
     * @param int $number
     * @param DatePeriod $datePeriod
     */
    public function __construct(?string $id, string $seasonId, int $number, DatePeriod $datePeriod)
    {
        $this->setId($id);
        $this->seasonId = $seasonId;
        $this->number = $number;
        $this->datePeriod = $datePeriod;
    }

    /**
     * @return string
     */
    public function getSeasonId(): string
    {
        return $this->seasonId;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return DatePeriod
     */
    public function getDatePeriod(): DatePeriod
    {
        return $this->datePeriod;
    }
}
