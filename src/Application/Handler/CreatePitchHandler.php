<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Pitch;

class CreatePitchHandler
{
    /** @var OrmRepositoryInterface */
    private $pitchRepository;

    /**
     * @param OrmRepositoryInterface $pitchRepository
     */
    public function __construct(OrmRepositoryInterface $pitchRepository)
    {
        $this->pitchRepository = $pitchRepository;
    }

    /**
     * @param CreatePitchCommand $command
     * @return string
     */
    public function handle(CreatePitchCommand $command)
    {
        $pitch = new Pitch($command->getLabel(), $command->getLocation());
        $this->pitchRepository->save($pitch);
        return $pitch->getId();
    }
}