<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class RenameTeamCommand implements CommandInterface
{
    /** @var string */
    private string $teamId;

    /** @var string */
    private string $newName;

    /**
     * @param string $teamId
     * @param string $newName
     */
    public function __construct(string $teamId, string $newName)
    {
        $this->teamId = $teamId;
        $this->newName = $newName;
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
    public function getNewName(): string
    {
        return $this->newName;
    }
}
