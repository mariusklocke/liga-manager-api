<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\Value\DatePeriod;
use HexagonalPlayground\Domain\Value\TeamIdPair;

class SetTournamentRoundCommand implements CommandInterface
{
    /** @var string */
    private string $tournamentId;

    /** @var int */
    private int $round;

    /** @var TeamIdPair[] */
    private array $teamIdPairs;

    /** @var DatePeriod */
    private DatePeriod $datePeriod;

    /**
     * @param string $tournamentId
     * @param int $round
     * @param TeamIdPair[] $teamIdPairs
     * @param DatePeriod $datePeriod
     */
    public function __construct(string $tournamentId, int $round, array $teamIdPairs, DatePeriod $datePeriod)
    {
        $this->tournamentId = $tournamentId;
        $this->round        = $round;
        $this->datePeriod   = $datePeriod;
        $this->teamIdPairs  = array_map(function (TeamIdPair $idPair) {
            return $idPair;
        }, $teamIdPairs);
    }

    /**
     * @return TeamIdPair[]
     */
    public function getTeamIdPairs(): array
    {
        return $this->teamIdPairs;
    }

    /**
     * @return string
     */
    public function getTournamentId(): string
    {
        return $this->tournamentId;
    }

    /**
     * @return int
     */
    public function getRound(): int
    {
        return $this->round;
    }

    /**
     * @return DatePeriod
     */
    public function getDatePeriod(): DatePeriod
    {
        return $this->datePeriod;
    }
}
