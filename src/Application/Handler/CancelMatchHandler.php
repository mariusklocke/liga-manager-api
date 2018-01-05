<?php

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Command\CancelMatchCommand;
use HexagonalDream\Application\Exception\NotFoundException;
use HexagonalDream\Application\Exception\PersistenceExceptionInterface;
use HexagonalDream\Application\ObjectPersistenceInterface;
use HexagonalDream\Domain\Match;

class CancelMatchHandler
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
     * @param CancelMatchCommand $command
     * @throws NotFoundException
     * @throws PersistenceExceptionInterface
     */
    public function handle(CancelMatchCommand $command)
    {
        $match = $this->persistence->find(Match::class, $command->getMatchId());
        $match->cancel();
    }
}
