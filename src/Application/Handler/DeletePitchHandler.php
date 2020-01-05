<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeletePitchCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;

class DeletePitchHandler implements AuthAwareHandler
{
    /** @var PitchRepositoryInterface */
    private $pitchRepository;

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
     */
    public function __invoke(DeletePitchCommand $command, AuthContext $authContext)
    {
        IsAdmin::check($authContext->getUser());
        $pitch = $this->pitchRepository->find($command->getPitchId());
        $pitch->assertDeletable();
        $this->pitchRepository->delete($pitch);
    }
}