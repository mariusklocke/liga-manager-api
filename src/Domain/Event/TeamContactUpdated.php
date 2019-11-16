<?php declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

use HexagonalPlayground\Domain\Value\ContactPerson;

class TeamContactUpdated extends Event
{
    public static function create(string $teamId, ?ContactPerson $old, ContactPerson $new): self
    {
        return self::createFromPayload([
            'teamId' => $teamId,
            'oldContact' => $old !== null ? $old->toArray() : null,
            'newContact' => $new->toArray()
        ]);
    }
}