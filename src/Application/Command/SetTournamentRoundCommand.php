<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use DateTimeImmutable;
use HexagonalPlayground\Domain\TeamIdPair;

class SetTournamentRoundCommand implements CommandInterface
{
    /** @var string */
    private $tournamentId;

    /** @var int */
    private $round;

    /** @var TeamIdPair[] */
    private $teamIdPairs;

    /** @var DateTimeImmutable|null */
    private $plannedFor;

    public function __construct(string $tournamentId, int $round, array $teamIdPairs, DateTimeImmutable $plannedFor = null)
    {
        $this->tournamentId = $tournamentId;
        $this->round        = $round;
        $this->plannedFor   = $plannedFor;
        $this->teamIdPairs  = array_map(function(array $pair) {
            return new TeamIdPair($pair['home_team_id'], $pair['guest_team_id']);
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
     * @return DateTimeImmutable|null
     */
    public function getPlannedFor()
    {
        return $this->plannedFor;
    }
}