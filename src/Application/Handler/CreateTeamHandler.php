<?php

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Command\CreateTeamCommand;
use HexagonalDream\Application\Exception\PersistenceExceptionInterface;
use HexagonalDream\Application\ObjectPersistenceInterface;
use HexagonalDream\Domain\Team;
use HexagonalDream\Domain\UuidGeneratorInterface;

class CreateTeamHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;
    /** @var UuidGeneratorInterface */
    private $uuidGenerator;

    /**
     * @param ObjectPersistenceInterface $persistence
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(ObjectPersistenceInterface $persistence, UuidGeneratorInterface $uuidGenerator)
    {
        $this->persistence = $persistence;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @param CreateTeamCommand $command
     * @throws PersistenceExceptionInterface
     */
    public function handle(CreateTeamCommand $command)
    {
        $team = new Team($this->uuidGenerator, $command->getTeamName());
        $this->persistence->persist($team);
    }
}
