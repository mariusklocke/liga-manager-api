<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\RenameTeamCommand;
use HexagonalPlayground\Application\Permission\CanManageTeam;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;

class RenameTeamHandler implements AuthAwareHandler
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
     * @param AuthContext $authContext
     */
    public function __invoke(RenameTeamCommand $command, AuthContext $authContext)
    {
        $team = $this->teamRepository->find($command->getTeamId());
        CanManageTeam::check($team, $authContext->getUser());
        $team->rename($command->getNewName());
    }
}