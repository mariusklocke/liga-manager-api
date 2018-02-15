<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteTeamCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Domain\Team;

class DeleteTeamHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    /**
     * @param ObjectPersistenceInterface $persistence
     */
    public function __construct(ObjectPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    /**
     * @param DeleteTeamCommand $command
     * @throws NotFoundException
     */
    public function handle(DeleteTeamCommand $command)
    {
        /** @var Team $team */
        $team = $this->persistence->find(Team::class, $command->getTeamId());
        $this->persistence->remove($team);
    }
}