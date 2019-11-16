<?php declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

use HexagonalPlayground\Domain\Value\ContactPerson;

class PitchContactUpdated extends Event
{
    public static function create(string $pitchId, ?ContactPerson $old, ContactPerson $new): self
    {
        return self::createFromPayload([
            'pitchId' => $pitchId,
            'oldContact' => $old !== null ? $old->toArray() : null,
            'newContact' => $new->toArray()
        ]);
    }
}