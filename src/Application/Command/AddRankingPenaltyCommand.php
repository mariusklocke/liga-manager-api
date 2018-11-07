<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class AddRankingPenaltyCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $seasonId;

    /** @var string */
    private $teamId;

    /** @var string */
    private $reason;

    /** @var int */
    private $points;

    /**
     * @param string $seasonId
     * @param string $teamId
     * @param string $reason
     * @param int $points
     */
    public function __construct(string $seasonId, string $teamId, string $reason, int $points)
    {
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