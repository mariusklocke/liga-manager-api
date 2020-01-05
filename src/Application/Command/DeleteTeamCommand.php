<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class DeleteTeamCommand implements CommandInterface
{
    /** @var string */
    private $teamId;

    /**
     * @param string $teamId
     */
    public function __construct(string $teamId)
    {
        $this->teamId = $teamId;
    }

    /**
     * @return string
     */
    public function getTeamId(): string
    {
        return $this->teamId;
    }
}