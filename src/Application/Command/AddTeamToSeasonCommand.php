<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class AddTeamToSeasonCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $seasonId;

    /** @var string */
    private $teamId;

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
