<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\DeletePitchCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Pitch;

class DeletePitchHandler implements AuthAwareHandler
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
     * @param DeletePitchCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(DeletePitchCommand $command, AuthContext $authContext): array
    {
        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        /** @var Pitch $pitch */
        $pitch = $this->pitchRepository->find($command->getId());
        $pitch->assertDeletable();
        $this->pitchRepository->delete($pitch);

        return [];
    }
}
