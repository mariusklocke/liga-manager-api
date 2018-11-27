<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

use HexagonalPlayground\Domain\MatchResult;

class MatchCancelled extends Event
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'match:cancelled';
    }

    public static function create(string $matchId, string $reason, MatchResult $previousResult)
    {
        return self::createFromPayload([
            'matchId' => $matchId,
            'reason'  => $reason,
            'homeScore' => $previousResult->getHomeScore(),
            'guestScore' => $previousResult->getGuestScore()
        ]);
    }
}