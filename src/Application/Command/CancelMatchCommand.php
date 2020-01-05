<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;

class CancelMatchCommand implements CommandInterface
{
    /** @var string */
    private $matchId;

    /** @var string */
    private $reason;

    /**
     * @param string $matchId
     * @param string $reason
     */
    public function __construct($matchId, $reason)
    {
        TypeAssert::assertString($matchId, 'matchId');
        TypeAssert::assertString($reason, 'reason');
        $this->matchId = $matchId;
        $this->reason  = $reason;
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
    public function getReason(): string
    {
        return $this->reason;
    }
}
