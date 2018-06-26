<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\Team;

class CreateTeamHandler
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
     * @param CreateTeamCommand $command
     * @return string Created team's ID
     */
    public function __invoke(CreateTeamCommand $command)
    {
        $team = new Team($command->getTeamName());
        $this->teamRepository->save($team);
        return $team->getId();
    }
}
