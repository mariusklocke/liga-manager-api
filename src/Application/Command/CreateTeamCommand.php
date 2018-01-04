<?php

namespace HexagonalDream\Application\Command;

class CreateTeamCommand implements CommandInterface
{
    /** @var string */
    private $teamName;

    public function __construct(string $teamName)
    {
        $this->teamName = $teamName;
    }

    public function getTeamName() : string
    {
        return $this->teamName;
    }
}
