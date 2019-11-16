<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\UpdatePitchContactCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Domain\Pitch;
use HexagonalPlayground\Domain\Value\ContactPerson;

class UpdatePitchContactHandler
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
     */
    public function __invoke(UpdatePitchContactCommand $command)
    {
        IsAdmin::check($command->getAuthenticatedUser());
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