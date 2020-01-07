<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;

class RenameTeamCommand implements CommandInterface
{
    /** @var string */
    private $teamId;

    /** @var string */
    private $newName;

    /**
     * @param string $teamId
     * @param string $newName
     */
    public function __construct($teamId, $newName)
    {
        TypeAssert::assertString($teamId, 'teamId');
        TypeAssert::assertString($newName, 'newName');
        $this->teamId = $teamId;
        $this->newName = $newName;
    }

    /**
     * @return string
     */
    public function getTeamId(): string
    {
        return $this->teamId;
    }

    /**
     * @return string
     */
    public function getNewName(): string
    {
        return $this->newName;
    }
}