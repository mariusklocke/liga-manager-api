<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
use HexagonalPlayground\Application\Exception\PermissionException;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\ContactPerson;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

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
        $this->checkPermission($command->getAuthenticatedUser(), $team);
        $contact = new ContactPerson(
            $command->getFirstName(),
            $command->getLastName(),
            $command->getPhone(),
            $command->getEmail()
        );
        $team->setContact($contact);
    }

    private function checkPermission(User $user, Team $team): void
    {
        if ($user->isInTeam($team) || $user->hasRole(User::ROLE_ADMIN)) {
            return;
        }

        throw new PermissionException('User is not permitted to change contact of this team');
    }
}