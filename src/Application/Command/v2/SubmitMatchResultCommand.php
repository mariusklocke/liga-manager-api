<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

use HexagonalPlayground\Domain\Value\MatchResult;

class SubmitMatchResultCommand implements CommandInterface
{
    /** @var string */
    private string $matchId;
    /** @var MatchResult */
    private MatchResult $matchResult;

    /**
     * @param string $matchId
     * @param MatchResult $matchResult
     */
    public function __construct(string $matchId, MatchResult $matchResult)
    {
        $this->matchId = $matchId;
        $this->matchResult = $matchResult;
    }

    /**
     * @return string
     */
    public function getMatchId(): string
    {
        return $this->matchId;
    }

    /**
     * @return MatchResult
     */
    public function getMatchResult(): MatchResult
    {
        return $this->matchResult;
    }
}
