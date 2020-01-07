<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Pitch;

class CreatePitchHandler implements AuthAwareHandler
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
     * @param CreatePitchCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(CreatePitchCommand $command, AuthContext $authContext)
    {
        IsAdmin::check($authContext->getUser());
        $pitch = new Pitch($command->getId(), $command->getLabel(), $command->getLocation());
        $this->pitchRepository->save($pitch);
    }
}