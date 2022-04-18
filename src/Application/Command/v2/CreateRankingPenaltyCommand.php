<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

class CreateRankingPenaltyCommand extends CreateCommand implements CommandInterface
{
    /** @var string */
    private string $seasonId;

    /** @var string */
    private string $teamId;

    /** @var string */
    private string $reason;

    /** @var int */
    private int $points;

    /**
     * @param string $id
     * @param string $seasonId
     * @param string $teamId
     * @param string $reason
     * @param int    $points
     */
    public function __construct(string $id, string $seasonId, string $teamId, string $reason, int $points)
    {
        $this->id = $id;
        $this->seasonId = $seasonId;
        $this->teamId = $teamId;
        $this->reason = $reason;
        $this->points = $points;
    }

    /**
     * @return string
     */
    public function getSeasonId(): string
    {
        return $this->seasonId;
    }

    /**
     * @return string
     */
    public function getTeamId(): string
    {
        return $this->teamId;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @return int
     */
    public function getPoints(): int
    {
        return $this->points;
    }
}
