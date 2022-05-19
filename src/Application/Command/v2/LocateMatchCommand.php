<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

class LocateMatchCommand implements CommandInterface
{
    /** @var string */
    private string $matchId;
    /** @var string */
    private string $pitchId;

    /**
     * @param string $matchId
     * @param string $pitchId
     */
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
