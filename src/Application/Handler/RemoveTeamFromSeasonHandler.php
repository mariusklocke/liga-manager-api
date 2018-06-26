<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;

class RemoveTeamFromSeasonHandler
{
    /** @var SeasonRepositoryInterface */
    private $seasonRepository;

    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /**
     * @param SeasonRepositoryInterface $seasonRepository
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(SeasonRepositoryInterface $seasonRepository, TeamRepositoryInterface $teamRepository)
    {
        $this->seasonRepository = $seasonRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param RemoveTeamFromSeasonCommand $command
     * @throws NotFoundException
     */
    public function __invoke(RemoveTeamFromSeasonCommand $command)
    {
        $season = $this->seasonRepository->find($command->getSeasonId());
        $team   = $this->teamRepository->find($command->getTeamId());

        $season->removeTeam($team);
    }
}