<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\UpdatePitchCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Pitch;

class UpdatePitchHandler implements AuthAwareHandler
{
    private PitchRepositoryInterface $pitchRepository;

    /**
     * @param PitchRepositoryInterface $pitchRepository
     */
    public function __construct(PitchRepositoryInterface $pitchRepository)
    {
        $this->pitchRepository = $pitchRepository;
    }

    public function __invoke(UpdatePitchCommand $command, AuthContext $authContext): array
    {
        /** @var Pitch $pitch */
        $pitch = $this->pitchRepository->find($command->getId());

        $pitch->setLabel($command->getLabel());
        $pitch->setContact($command->getContact());
        $pitch->setLocation($command->getLocation());

        $this->pitchRepository->save($pitch);

        return [];
    }
}
