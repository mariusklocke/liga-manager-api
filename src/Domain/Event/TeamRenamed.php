<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

class TeamRenamed extends Event
{
    /**
     * @param string $teamId
     * @param string $oldName
     * @param string $newName
     * @return TeamRenamed
     */
    public static function create(string $teamId, string $oldName, string $newName): self
    {
        return self::createFromPayload([
            'teamId' => $teamId,
            'oldName' => $oldName,
            'newName' => $newName
        ]);
    }
}
