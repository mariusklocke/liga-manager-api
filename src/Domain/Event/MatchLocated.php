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

    /**
     * @return string
     */
    public function getMatchId(): string
    {
        return $this->payload['matchId'];
    }

    /**
     * @return string
     */
    public function getPitchId(): string
    {
        return $this->payload['pitchId'];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'match:located';
    }
}