<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;

class MatchScheduled extends DomainEvent
{
    /** @var string */
    private $matchId;

    /** @var DateTimeImmutable */
    private $kickoff;

    public function __construct(string $matchId, DateTimeImmutable $kickoff)
    {
        parent::__construct();
        $this->matchId = $matchId;
        $this->kickoff = $kickoff;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['matchId'] = $this->matchId;
        $array['kickoff'] = $this->kickoff->format(DATE_ATOM);

        return $array;
    }
}