<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\UpdatePitchContactCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Pitch;
use HexagonalPlayground\Domain\Value\ContactPerson;

class UpdatePitchContactHandler implements AuthAwareHandler
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
     * @param UpdatePitchContactCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(UpdatePitchContactCommand $command, AuthContext $authContext): array
    {
        $events = [];

        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();
        /** @var Pitch $pitch */
        $pitch = $this->pitchRepository->find($command->getPitchId());
        $oldContact = $pitch->getContact();
        $newContact = new ContactPerson(
            $command->getFirstName(),
            $command->getLastName(),
            $command->getPhone(),
            $command->getEmail()
        );

        if ($oldContact === null || !$oldContact->equals($newContact)) {
            $pitch->setContact($newContact);

            $events[] = new Event('pitch:contact:updated', [
                'pitchId' => $pitch->getId(),
                'oldContact' => $oldContact !== null ? $oldContact->toArray() : null,
                'newContact' => $newContact->toArray()
            ]);
        }

        return $events;
    }
}
