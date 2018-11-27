<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class RenameTeamCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $teamId;

    /** @var string */
    private $newName;

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