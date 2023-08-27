<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\CreatePitchCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Pitch;

class CreatePitchHandler implements AuthAwareHandler
{
    /** @var PitchRepositoryInterface */
    private PitchRepositoryInterface $pitchRepository;

    /**
     * @param PitchRepositoryInterface $pitchRepository
     */
    public function __construct(PitchRepositoryInterface $pitchRepository)
    {
        $this->pitchRepository = $pitchRepository;
    }

    /**
     * @param CreatePitchCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(CreatePitchCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        $pitch = new Pitch($command->getId(), $command->getLabel(), $command->getLocation(), $command->getContact());
        $this->pitchRepository->save($pitch);

        return [];
    }
}
