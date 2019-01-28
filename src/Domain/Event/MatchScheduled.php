<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

use DateTimeImmutable;

class MatchScheduled extends Event
{
    /**
     * @param string $matchId
     * @param DateTimeImmutable $kickoff
     * @return self
     */
    public static function create(string $matchId, DateTimeImmutable $kickoff): self
    {
        return self::createFromPayload([
            'matchId' => $matchId,
            'kickoff' => $kickoff->getTimestamp()
        ]);
    }
}
