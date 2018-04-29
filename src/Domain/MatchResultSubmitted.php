<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

class MatchResultSubmitted extends DomainEvent
{
    /** @var string */
    private $matchId;

    /** @var MatchResult */
    private $matchResult;

    /** @var string */
    private $userId;

    public function __construct(string $matchId, MatchResult $matchResult, string $userId)
    {
        parent::__construct();
        $this->matchId     = $matchId;
        $this->matchResult = $matchResult;
        $this->userId      = $userId;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['matchId'] = $this->matchId;
        $array['matchResult'] = $this->matchResult->toArray();
        $array['userId'] = $this->userId;

        return $array;
    }
}