<?php declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

class SeasonCreated extends Event
{
    public static function create(string $seasonId): self
    {
        return self::createFromPayload([
            'seasonId' => $seasonId
        ]);
    }
}