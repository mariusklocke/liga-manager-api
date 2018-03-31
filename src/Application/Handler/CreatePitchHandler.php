<?php

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Application\IdGeneratorInterface;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Domain\Pitch;

class CreatePitchHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    /** @var IdGeneratorInterface */
    private $idGenerator;

    /**
     * @param ObjectPersistenceInterface $persistence
     * @param IdGeneratorInterface       $idGenerator
     */
    public function __construct(ObjectPersistenceInterface $persistence, IdGeneratorInterface $idGenerator)
    {
        $this->persistence = $persistence;
        $this->idGenerator = $idGenerator;
    }

    /**
     * @param CreatePitchCommand $command
     * @return string
     */
    public function handle(CreatePitchCommand $command)
    {
        $pitch = new Pitch($this->idGenerator->generate(), $command->getLabel(), $command->getLocation());
        $this->persistence->persist($pitch);
        return $pitch->getId();
    }
}