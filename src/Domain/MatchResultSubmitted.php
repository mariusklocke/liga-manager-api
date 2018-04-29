<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

class MatchResultSubmitted extends DomainEvent
{
    /** @var string */
    private $matchId;

    /** @var MatchResult */
    private $matchResult;

    public function __construct(string $matchId, MatchResult $matchResult)
    {
        parent::__construct();
        $this->matchId     = $matchId;
        $this->matchResult = $matchResult;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['matchId'] = $this->matchId;
        $array['matchResult'] = $this->matchResult->toArray();

        return $array;
    }
}