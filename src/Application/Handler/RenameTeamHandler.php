<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\RenameTeamCommand;
use HexagonalPlayground\Application\Permission\CanManageTeam;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Team;

class RenameTeamHandler implements AuthAwareHandler
{
    /** @var TeamRepositoryInterface */
    private TeamRepositoryInterface $teamRepository;

    /**
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param RenameTeamCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(RenameTeamCommand $command, AuthContext $authContext): array
    {
        $events = [];

        /** @var Team $team */
        $team = $this->teamRepository->find($command->getTeamId());
        $canManageTeam = new CanManageTeam($team, $authContext->getUser());
        $canManageTeam->check();

        $oldName = $team->getName();

        if ($oldName !== $command->getNewName()) {
            $team->setName($command->getNewName());
            $events[] = new Event('team:renamed', [
                'teamId' => $team->getId(),
                'oldName' => $oldName,
                'newName' => $team->getName()
            ]);
        }

        return $events;
    }
}
