<?php

namespace HexagonalDream\Application\Command;

class CancelMatchCommand implements CommandInterface
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
