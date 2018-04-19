<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\ContactPerson;
use HexagonalPlayground\Domain\Team;

class UpdateTeamContactHandler
{
    /** @var OrmRepositoryInterface */
    private $teamRepository;

    /**
     * @param OrmRepositoryInterface $teamRepository
     */
    public function __construct(OrmRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param UpdateTeamContactCommand $command
     */
    public function handle(UpdateTeamContactCommand $command)
    {
        /** @var Team $team */
        $team = $this->teamRepository->find($command->getTeamId());
        $contact = new ContactPerson(
            $command->getFirstName(),
            $command->getLastName(),
            $command->getPhone(),
            $command->getEmail()
        );
        $team->setContact($contact);
    }
}