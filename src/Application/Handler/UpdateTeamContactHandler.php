<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\ContactPerson;
use HexagonalPlayground\Domain\Team;

class UpdateTeamContactHandler
{
    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /**
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param UpdateTeamContactCommand $command
     */
    public function __invoke(UpdateTeamContactCommand $command)
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