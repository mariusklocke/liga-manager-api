<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\UpdatePitchContactCommand;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\ContactPerson;
use HexagonalPlayground\Domain\Pitch;

class UpdatePitchContactHandler
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
     * @param UpdatePitchContactCommand $command
     */
    public function handle(UpdatePitchContactCommand $command)
    {
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