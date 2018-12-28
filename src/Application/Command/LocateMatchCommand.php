<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;

class LocateMatchCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $matchId;
    /** @var string */
    private $pitchId;

    /**
     * @param string $matchId
     * @param string $pitchId
     */
    public function __construct($matchId, $pitchId)
    {
        TypeAssert::assertString($matchId, 'matchId');
        TypeAssert::assertString($pitchId, 'pitchId');
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
