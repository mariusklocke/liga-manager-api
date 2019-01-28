<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

class MatchLocated extends Event
{
    /**
     * @param string $matchId
     * @param string $pitchId
     * @return self
     */
    public static function create(string $matchId, string $pitchId): self
    {
        return self::createFromPayload([
            'matchId' => $matchId,
            'pitchId' => $pitchId
        ]);
    }
}
