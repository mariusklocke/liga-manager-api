<?php

namespace HexagonalDream\Application\Command;

class CancelMatchCommand
{
    /** @var string */
    private $matchId;

    public function __construct(string $matchId)
    {
        $this->matchId = $matchId;
    }

    /**
     * @return string
     */
    public function getMatchId(): string
    {
        return $this->matchId;
    }
}
