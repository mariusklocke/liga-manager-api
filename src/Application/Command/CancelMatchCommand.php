<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class CancelMatchCommand implements CommandInterface
{
    use AuthenticationAware;

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
