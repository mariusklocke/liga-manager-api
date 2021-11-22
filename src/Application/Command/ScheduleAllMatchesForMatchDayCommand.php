<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\Value\MatchAppointment;

class ScheduleAllMatchesForMatchDayCommand implements CommandInterface
{
    /** @var string */
    private $matchDayId;

    /** @var MatchAppointment[] */
    private $matchAppointments;

    /**
     * @param string $matchDayId
     * @param MatchAppointment[] $matchAppointments
     */
    public function __construct(string $matchDayId, array $matchAppointments)
    {
        $this->matchDayId = $matchDayId;
        $this->matchAppointments = $matchAppointments;
    }

    /**
     * @return string
     */
    public function getMatchDayId(): string
    {
        return $this->matchDayId;
    }

    /**
     * @return MatchAppointment[]
     */
    public function getMatchAppointments(): array
    {
        return $this->matchAppointments;
    }
}
