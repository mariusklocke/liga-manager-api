<?php
/**
 * DeleteTeamCommand.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Application\Command;


class DeleteTeamCommand
{
    /** @var string */
    private $teamId;

    public function __construct(string $teamId)
    {
        $this->teamId = $teamId;
    }

    /**
     * @return string
     */
    public function getTeamId(): string
    {
        return $this->teamId;
    }
}