<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use DateTimeImmutable;

class SetTournamentRoundCommand implements CommandInterface
{
    /** @var string */
    private $tournamentId;

    /** @var int */
    private $round;

    /** @var array */
    private $teamIdPairs;

    /** @var DateTimeImmutable|null */
    private $plannedFor;

    public function __construct(string $tournamentId, int $round, DateTimeImmutable $plannedFor = null)
    {
        $this->tournamentId = $tournamentId;
        $this->round = $round;
        $this->plannedFor = $plannedFor;
        $this->teamIdPairs = [];
    }

    /**
     * @param string $homeTeamId
     * @param string $guestTeamId
     */
    public function addPair(string $homeTeamId, string $guestTeamId)
    {
        $this->teamIdPairs[] = [$homeTeamId, $guestTeamId];
    }

    /**
     * @return array
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
     * @return DateTimeImmutable|null
     */
    public function getPlannedFor()
    {
        return $this->plannedFor;
    }
}