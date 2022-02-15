<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class RemoveTeamFromSeasonCommand implements CommandInterface
{
    /** @var string */
    private string $seasonId;

    /** @var string */
    private string $teamId;

    /**
     * @param string $seasonId
     * @param string $teamId
     */
    public function __construct(string $seasonId, string $teamId)
    {
        $this->seasonId = $seasonId;
        $this->teamId   = $teamId;
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
}