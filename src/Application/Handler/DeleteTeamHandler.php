<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteTeamCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Team;

class DeleteTeamHandler
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
     * @param DeleteTeamCommand $command
     * @throws NotFoundException
     */
    public function __invoke(DeleteTeamCommand $command)
    {
        /** @var Team $team */
        $team = $this->teamRepository->find($command->getTeamId());
        $this->teamRepository->delete($team);
    }
}