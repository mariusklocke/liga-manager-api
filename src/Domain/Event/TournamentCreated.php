<?php declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

class TournamentCreated extends Event
{
    public static function create(string $tournamentId): self
    {
        return self::createFromPayload([
            'tournamentId' => $tournamentId
        ]);
    }
}