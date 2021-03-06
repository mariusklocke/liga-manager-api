<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\UpdatePitchContactCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Pitch;
use HexagonalPlayground\Domain\Value\ContactPerson;

class UpdatePitchContactHandler implements AuthAwareHandler
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
     * @param UpdatePitchContactCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(UpdatePitchContactCommand $command, AuthContext $authContext): void
    {
        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();
        /** @var Pitch $pitch */
        $pitch = $this->pitchRepository->find($command->getPitchId());
        $contact = new ContactPerson(
            $command->getFirstName(),
            $command->getLastName(),
            $command->getPhone(),
            $command->getEmail()
        );
        $pitch->setContact($contact);
    }
}