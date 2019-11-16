<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
use HexagonalPlayground\Application\Permission\CanManageTeam;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Value\ContactPerson;

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
        CanManageTeam::check($team, $command->getAuthenticatedUser());
        $contact = new ContactPerson(
            $command->getFirstName(),
            $command->getLastName(),
            $command->getPhone(),
            $command->getEmail()
        );
        $team->setContact($contact);
    }
}