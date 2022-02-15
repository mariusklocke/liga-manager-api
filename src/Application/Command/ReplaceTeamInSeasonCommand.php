<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class ReplaceTeamInSeasonCommand implements CommandInterface
{
    /** @var string */
    private string $seasonId;
    /** @var string */
    private string $currentTeamId;
    /** @var string */
    private string $replacementTeamId;

    /**
     * @param string $seasonId
     * @param string $currentTeamId
     * @param string $replacementTeamId
     */
    public function __construct(string $seasonId, string $currentTeamId, string $replacementTeamId)
    {
        $this->seasonId = $seasonId;
        $this->currentTeamId = $currentTeamId;
        $this->replacementTeamId = $replacementTeamId;
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
    public function getCurrentTeamId(): string
    {
        return $this->currentTeamId;
    }

    /**
     * @return string
     */
    public function getReplacementTeamId(): string
    {
        return $this->replacementTeamId;
    }
}
