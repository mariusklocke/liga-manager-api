<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\RenameTeamCommand;
use HexagonalPlayground\Application\Permission\CanManageTeam;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;

class RenameTeamHandler
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
     * @param RenameTeamCommand $command
     */
    public function __invoke(RenameTeamCommand $command)
    {
        $team = $this->teamRepository->find($command->getTeamId());
        CanManageTeam::check($team, $command->getAuthenticatedUser());
        $team->rename($command->getNewName());
    }
}