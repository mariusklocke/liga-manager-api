<?php

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Command\DeleteSeasonCommand;
use HexagonalDream\Application\Exception\InvalidStateException;
use HexagonalDream\Application\ObjectPersistenceInterface;
use HexagonalDream\Domain\Season;

class DeleteSeasonHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    public function __construct(ObjectPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    public function handle(DeleteSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->persistence->find(Season::class, $command->getSeasonId());
        if ($season->hasStarted()) {
            throw new InvalidStateException('Cannot delete a season which has already started');
        }
        $season->clearMatches()->clearTeams();
        $this->persistence->remove($season);
    }
}
