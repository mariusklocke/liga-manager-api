<?php

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Command\ScheduleMatchCommand;
use HexagonalDream\Application\Exception\NotFoundException;
use HexagonalDream\Application\Exception\PersistenceExceptionInterface;
use HexagonalDream\Application\ObjectPersistenceInterface;
use HexagonalDream\Domain\DomainException;
use HexagonalDream\Domain\Match;

class ScheduleMatchHandler
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
     * @param ScheduleMatchCommand $command
     * @throws NotFoundException
     * @throws PersistenceExceptionInterface
     * @throws DomainException
     */
    public function handle(ScheduleMatchCommand $command)
    {
        /** @var Match $match */
        $match = $this->persistence->find(Match::class, $command->getMatchId());
        $match->schedule($command->getKickoff());
    }
}
