<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeletePitchCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;

class DeletePitchHandler
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
     */
    public function __invoke(DeletePitchCommand $command)
    {
        IsAdmin::check($command->getAuthenticatedUser());
        $pitch = $this->pitchRepository->find($command->getPitchId());
        $this->pitchRepository->delete($pitch);
    }
}