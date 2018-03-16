<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Application\IdGeneratorInterface;
use HexagonalPlayground\Domain\Team;

class CreateTeamHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;
    /** @var IdGeneratorInterface */
    private $uuidGenerator;

    /**
     * @param ObjectPersistenceInterface $persistence
     * @param IdGeneratorInterface $uuidGenerator
     */
    public function __construct(ObjectPersistenceInterface $persistence, IdGeneratorInterface $uuidGenerator)
    {
        $this->persistence = $persistence;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @param CreateTeamCommand $command
     * @return string Created team's ID
     */
    public function handle(CreateTeamCommand $command)
    {
        $team = new Team($this->uuidGenerator->generate(), $command->getTeamName());
        $this->persistence->persist($team);
        return $team->getId();
    }
}
