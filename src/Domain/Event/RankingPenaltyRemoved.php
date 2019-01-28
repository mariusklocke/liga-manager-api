<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

class RankingPenaltyRemoved extends Event
{
    /**
     * @param string $seasonId
     * @param string $teamId
     * @param string $reason
     * @param int    $points
     * @param string $userId
     * @return RankingPenaltyRemoved
     */
    public static function create(string $seasonId, string $teamId, string $reason, int $points, string $userId): self
    {
        return self::createFromPayload([
            'seasonId'   => $seasonId,
            'teamId'     => $teamId,
            'reason'     => $reason,
            'points'     => $points,
            'userId'     => $userId
        ]);
    }
}