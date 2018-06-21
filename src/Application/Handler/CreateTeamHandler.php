<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Team;

class CreateTeamHandler
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
