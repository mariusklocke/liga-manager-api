<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class SetTournamentRoundCommand implements CommandInterface
{
    /** @var string */
    private $tournamentId;

    /** @var int */
    private $round;

    /** @var array */
    private $teamIdPairs;

    public function __construct(string $tournamentId, int $round)
    {
        $this->tournamentId = $tournamentId;
        $this->round = $round;
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
}