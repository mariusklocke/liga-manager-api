<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Application\IdGeneratorInterface;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Pitch;

class CreatePitchHandler
{
    /** @var OrmRepositoryInterface */
    private $pitchRepository;

    /** @var IdGeneratorInterface */
    private $idGenerator;

    /**
     * @param OrmRepositoryInterface $pitchRepository
     * @param IdGeneratorInterface $idGenerator
     */
    public function __construct(OrmRepositoryInterface $pitchRepository, IdGeneratorInterface $idGenerator)
    {
        $this->pitchRepository = $pitchRepository;
        $this->idGenerator = $idGenerator;
    }

    /**
     * @param CreatePitchCommand $command
     * @return string
     */
    public function handle(CreatePitchCommand $command)
    {
        $pitch = new Pitch($this->idGenerator->generate(), $command->getLabel(), $command->getLocation());
        $this->pitchRepository->save($pitch);
        return $pitch->getId();
    }
}