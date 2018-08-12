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

    /**
     * @return string
     */
    public function getMatchId(): string
    {
        return $this->payload['matchId'];
    }

    /**
     * @return int
     */
    public function getHomeScore(): int
    {
        return $this->payload['homeScore'];
    }

    /**
     * @return int
     */
    public function getGuestScore(): int
    {
        return $this->payload['guestScore'];
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->payload['userId'];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'match:result:submitted';
    }
}