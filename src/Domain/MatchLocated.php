<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

class MatchLocated extends DomainEvent
{
    /** @var string */
    private $matchId;

    /** @var string */
    private $pitchId;

    public function __construct(string $matchId, string $pitchId)
    {
        parent::__construct();
        $this->matchId = $matchId;
        $this->pitchId = $pitchId;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['matchId'] = $this->matchId;
        $array['pitchId'] = $this->pitchId;

        return $array;
    }
}