<?php

namespace HexagonalPlayground\Application\Command;

class LocateMatchCommand implements CommandInterface
{
    /** @var string */
    private $matchId;
    /** @var string */
    private $pitchId;

    public function __construct(string $matchId, string $pitchId)
    {
        $this->matchId = $matchId;
        $this->pitchId = $pitchId;
    }

    /**
     * @return string
     */
    public function getMatchId(): string
    {
        return $this->matchId;
    }

    /**
     * @return string
     */
    public function getPitchId(): string
    {
        return $this->pitchId;
    }
}
