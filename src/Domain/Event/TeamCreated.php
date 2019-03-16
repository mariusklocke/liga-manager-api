<?php declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

class TeamCreated extends Event
{
    public static function create(string $teamId): self
    {
        return self::createFromPayload([
            'teamId' => $teamId
        ]);
    }
}