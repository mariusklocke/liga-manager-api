<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

class MatchResultSubmitted extends Event
{
    public static function create(string $matchId, int $homeScore, int $guestScore, string $userId): self
    {
        return self::createFromPayload([
            'matchId'    => $matchId,
            'homeScore'  => $homeScore,
            'guestScore' => $guestScore,
            'userId'     => $userId
        ]);
    }
}
