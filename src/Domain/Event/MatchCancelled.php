<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

use HexagonalPlayground\Domain\Value\MatchResult;

class MatchCancelled extends Event
{
    public static function create(string $matchId, string $reason, ?MatchResult $previousResult)
    {
        return self::createFromPayload([
            'matchId' => $matchId,
            'reason'  => $reason,
            'homeScore' => null !== $previousResult ? $previousResult->getHomeScore() : null,
            'guestScore' => null !== $previousResult ? $previousResult->getGuestScore() : null
        ]);
    }
}