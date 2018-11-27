<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class CancelMatchCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $matchId;

    /** @var string */
    private $reason;

    public function __construct(string $matchId, string $reason)
    {
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
