<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

use HexagonalPlayground\Domain\Value\DatePeriod;

class CreateMatchDayCommand extends CreateCommand implements CommandInterface
{
    /** @var string|null */
    private ?string $seasonId;

    /** @var string|null */
    private ?string $tournamentId;

    /** @var int */
    private int $number;

    /** @var DatePeriod */
    private DatePeriod $datePeriod;

    /**
     * @param string $id
     * @param string|null $seasonId
     * @param string|null $tournamentId
     * @param int $number
     * @param DatePeriod $datePeriod
     */
    public function __construct(string $id, ?string $seasonId, ?string $tournamentId, int $number, DatePeriod $datePeriod)
    {
        $this->id = $id;
        $this->seasonId = $seasonId;
        $this->tournamentId = $tournamentId;
        $this->number = $number;
        $this->datePeriod = $datePeriod;
    }

    /**
     * @return string|null
     */
    public function getSeasonId(): ?string
    {
        return $this->seasonId;
    }

    /**
     * @return string|null
     */
    public function getTournamentId(): ?string
    {
        return $this->tournamentId;
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
