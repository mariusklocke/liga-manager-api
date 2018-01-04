<?php
/**
 * DeleteTeamHandler.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Command\DeleteTeamCommand;
use HexagonalDream\Application\Exception\NotFoundException;
use HexagonalDream\Application\Exception\PersistenceExceptionInterface;
use HexagonalDream\Application\ObjectPersistenceInterface;
use HexagonalDream\Domain\Team;

class DeleteTeamHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    public function __construct(ObjectPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    /**
     * @param DeleteTeamCommand $command
     * @throws PersistenceExceptionInterface
     * @throws NotFoundException
     */
    public function handle(DeleteTeamCommand $command)
    {
        $this->persistence->transactional(function() use ($command) {
            $team = $this->persistence->find(Team::class, $command->getTeamId());
            $this->persistence->remove($team);
        });
    }
}