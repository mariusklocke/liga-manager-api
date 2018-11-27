<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

class TeamRenamed extends Event
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'team:renamed';
    }

    public static function create(string $teamId, string $oldName, string $newName): self
    {
        return self::createFromPayload([
            'teamId' => $teamId,
            'oldName' => $oldName,
            'newName' => $newName
        ]);
    }
}