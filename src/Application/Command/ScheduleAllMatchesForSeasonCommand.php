<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\Value\MatchAppointment;

class ScheduleAllMatchesForSeasonCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $seasonId;

    /** @var MatchAppointment[] */
    private $matchAppointments;

    /**
     * @param string $seasonId
     * @param MatchAppointment[] $matchAppointments
     */
    public function __construct(string $seasonId, array $matchAppointments)
    {
        $this->seasonId = $seasonId;
        $this->matchAppointments = $matchAppointments;
    }

    /**
     * @return string
     */
    public function getSeasonId(): string
    {
        return $this->seasonId;
    }

    /**
     * @return MatchAppointment[]
     */
    public function getMatchAppointments(): array
    {
        return $this->matchAppointments;
    }
}
