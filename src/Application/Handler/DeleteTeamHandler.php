<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteTeamCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;

class DeleteTeamHandler
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
     * @param DeleteTeamCommand $command
     * @throws NotFoundException
     */
    public function __invoke(DeleteTeamCommand $command)
    {
        $team = $this->teamRepository->find($command->getTeamId());
        $this->teamRepository->delete($team);
    }
}